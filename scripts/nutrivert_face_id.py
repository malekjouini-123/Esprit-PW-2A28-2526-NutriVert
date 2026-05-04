#!/usr/bin/env python3
"""NutriVert Face ID CLI using simple OpenCV logic.

PHP calls this script with exec(), then reads one JSON object from stdout.
The stored encoding is a normalized grayscale face vector, serialized as JSON.
"""

import argparse
import json
import os
import sys
from typing import Any, Dict, List, Optional

try:
    import cv2
    import numpy as np
except Exception as import_error:  # pragma: no cover
    cv2 = None
    np = None
    CV_IMPORT_ERROR = str(import_error)
else:
    CV_IMPORT_ERROR = None

FACE_SIZE = (100, 100)
MATCH_THRESHOLD = 0.38


def json_exit(payload: Dict[str, Any], exit_code: int = 0) -> None:
    print(json.dumps(payload))
    sys.exit(exit_code)


def ensure_cv() -> None:
    if cv2 is None or np is None:
        json_exit(
            {
                "success": False,
                "match": False,
                "error": "La librairie opencv-python n'est pas disponible.",
                "details": CV_IMPORT_ERROR,
            },
            2,
        )


def normalize_encoding(value: Any) -> Optional[List[float]]:
    if isinstance(value, str):
        try:
            value = json.loads(value)
        except json.JSONDecodeError:
            return None

    if not isinstance(value, list):
        return None

    try:
        normalized = [float(item) for item in value]
    except (TypeError, ValueError):
        return None

    return normalized if len(normalized) == FACE_SIZE[0] * FACE_SIZE[1] else None


def load_gray_image(image_path: str):
    ensure_cv()

    if not os.path.isfile(image_path):
        json_exit({"success": False, "match": False, "error": "Image introuvable."}, 2)

    image = cv2.imread(image_path)
    if image is None:
        json_exit({"success": False, "match": False, "error": "Image illisible."}, 2)

    return cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)


def detect_largest_face(gray_image):
    cascade_path = os.path.join(cv2.data.haarcascades, "haarcascade_frontalface_default.xml")
    detector = cv2.CascadeClassifier(cascade_path)
    if detector.empty():
        json_exit({"success": False, "match": False, "error": "Cascade OpenCV introuvable."}, 2)

    faces = detector.detectMultiScale(
        gray_image,
        scaleFactor=1.1,
        minNeighbors=5,
        minSize=(60, 60),
    )

    if len(faces) == 0:
        json_exit({"success": False, "match": False, "error": "Aucun visage detecte."}, 3)

    return max(faces, key=lambda face: face[2] * face[3])


def encode_image(image_path: str) -> List[float]:
    gray = load_gray_image(image_path)
    x, y, width, height = detect_largest_face(gray)
    face = gray[y : y + height, x : x + width]
    face = cv2.equalizeHist(face)
    face = cv2.resize(face, FACE_SIZE, interpolation=cv2.INTER_AREA)
    face = face.astype("float32") / 255.0
    return face.flatten().round(6).tolist()


def compare_encodings(known: List[float], captured: List[float]) -> float:
    known_array = np.array(known, dtype="float32")
    captured_array = np.array(captured, dtype="float32")
    return float(np.mean((known_array - captured_array) ** 2))


def confidence_from_distance(distance: float, threshold: float) -> float:
    return round(max(0.0, min(1.0, 1.0 - (distance / threshold))), 4)


def command_encode(args: argparse.Namespace) -> None:
    encoding = encode_image(args.image)
    json_exit({"success": True, "match": False, "encoding": encoding})


def command_compare(args: argparse.Namespace) -> None:
    captured = encode_image(args.image)
    known = normalize_encoding(args.encoding)

    if known is None:
        json_exit({"success": False, "match": False, "error": "Encodage stocke invalide."}, 2)

    distance = compare_encodings(known, captured)
    matched = distance <= args.threshold
    json_exit(
        {
            "success": True,
            "match": matched,
            "confidence": confidence_from_distance(distance, args.threshold),
            "distance": round(distance, 6),
            "user_id": args.user_id if matched else None,
        }
    )


def command_identify(args: argparse.Namespace) -> None:
    captured = encode_image(args.image)

    if not os.path.isfile(args.known_faces):
        json_exit({"success": False, "match": False, "error": "Fichier des visages connus introuvable."}, 2)

    with open(args.known_faces, "r", encoding="utf-8") as handle:
        known_faces = json.load(handle)

    best_user_id = None
    best_distance = None

    for row in known_faces:
        known = normalize_encoding(row.get("face_encoding"))
        if known is None:
            continue

        distance = compare_encodings(known, captured)
        if best_distance is None or distance < best_distance:
            best_distance = distance
            best_user_id = int(row.get("user_id") or row.get("id_utilisateur"))

    if best_distance is None:
        json_exit({"success": False, "match": False, "error": "Aucun encodage utilisable."}, 2)

    matched = best_distance <= args.threshold
    json_exit(
        {
            "success": True,
            "match": matched,
            "confidence": confidence_from_distance(best_distance, args.threshold),
            "distance": round(best_distance, 6),
            "user_id": best_user_id if matched else None,
        }
    )


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(description="NutriVert Face ID OpenCV")
    subparsers = parser.add_subparsers(dest="command", required=True)

    encode_parser = subparsers.add_parser("encode")
    encode_parser.add_argument("--image", required=True)
    encode_parser.set_defaults(func=command_encode)

    compare_parser = subparsers.add_parser("compare")
    compare_parser.add_argument("--image", required=True)
    compare_parser.add_argument("--encoding", required=True)
    compare_parser.add_argument("--user-id", type=int)
    compare_parser.add_argument("--threshold", type=float, default=MATCH_THRESHOLD)
    compare_parser.set_defaults(func=command_compare)

    identify_parser = subparsers.add_parser("identify")
    identify_parser.add_argument("--image", required=True)
    identify_parser.add_argument("--known-faces", required=True)
    identify_parser.add_argument("--threshold", type=float, default=MATCH_THRESHOLD)
    identify_parser.set_defaults(func=command_identify)

    return parser


def main() -> None:
    parser = build_parser()
    args = parser.parse_args()
    try:
        args.func(args)
    except Exception as error:  # pragma: no cover
        json_exit({"success": False, "match": False, "error": str(error)}, 1)


if __name__ == "__main__":
    main()
