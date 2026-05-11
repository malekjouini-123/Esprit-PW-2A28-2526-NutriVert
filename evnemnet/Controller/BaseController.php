<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

abstract class BaseController
{
    protected function eventGetAll(): array
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->query("
                SELECT e.*, c.nom AS categorie_nom
                FROM evenements e
                LEFT JOIN categories c ON e.categorie_id = c.id
                ORDER BY e.date_evenement DESC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::eventGetAll() - ' . $e->getMessage());
            return [];
        }
    }

    protected function eventGetAllPublished(): array
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->query("
                SELECT e.*, c.nom AS categorie_nom
                FROM evenements e
                LEFT JOIN categories c ON e.categorie_id = c.id
                WHERE e.is_published = 1
                ORDER BY e.date_evenement DESC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            log_message('WARNING', 'BaseController::eventGetAllPublished() - ' . $e->getMessage());
            return $this->eventGetAll();
        }
    }

    protected function eventGetById(int $id): ?array
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->prepare("
                SELECT e.*, c.nom AS categorie_nom
                FROM evenements e
                LEFT JOIN categories c ON e.categorie_id = c.id
                WHERE e.id = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::eventGetById(' . $id . ') - ' . $e->getMessage());
            return null;
        }
    }

    protected function eventCreate(array $data): bool
    {
        $data['is_published'] = isset($data['is_published']) ? (int)(bool)$data['is_published'] : 1;
        try {
            $pdo = get_pdo();
            $sql = "INSERT INTO evenements (titre, description, date_evenement, lieu, prix, capacite, categorie_id, image_url, is_published)
                    VALUES (:titre, :description, :date_evenement, :lieu, :prix, :capacite, :categorie_id, :image_url, :is_published)";
            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute($data);
            if ($ok) {
                log_message('INFO', 'Événement créé: ' . ($data['titre'] ?? ''));
            }
            return $ok;
        } catch (PDOException $e) {
            try {
                $pdo = get_pdo();
                $sql = "INSERT INTO evenements (titre, description, date_evenement, lieu, prix, capacite, categorie_id, image_url)
                        VALUES (:titre, :description, :date_evenement, :lieu, :prix, :capacite, :categorie_id, :image_url)";
                $stmt = $pdo->prepare($sql);
                unset($data['is_published']);
                $ok = $stmt->execute($data);
                if ($ok) {
                    log_message('INFO', 'Événement créé (sans is_published): ' . ($data['titre'] ?? ''));
                }
                return $ok;
            } catch (PDOException $e2) {
                log_message('ERROR', 'BaseController::eventCreate() - ' . $e2->getMessage());
                return false;
            }
        }
    }

    protected function eventUpdate(int $id, array $data): bool
    {
        $data['id'] = $id;
        try {
            $pdo = get_pdo();
            $sql = "UPDATE evenements SET
                    titre = :titre,
                    description = :description,
                    date_evenement = :date_evenement,
                    lieu = :lieu,
                    prix = :prix,
                    capacite = :capacite,
                    categorie_id = :categorie_id,
                    image_url = :image_url,
                    is_published = :is_published
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            try {
                $pdo = get_pdo();
                unset($data['is_published']);
                $sql = "UPDATE evenements SET
                        titre = :titre,
                        description = :description,
                        date_evenement = :date_evenement,
                        lieu = :lieu,
                        prix = :prix,
                        capacite = :capacite,
                        categorie_id = :categorie_id,
                        image_url = :image_url
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                return $stmt->execute($data);
            } catch (PDOException $e2) {
                log_message('ERROR', 'BaseController::eventUpdate(' . $id . ') - ' . $e2->getMessage());
                return false;
            }
        }
    }

    protected function eventDelete(int $id): bool
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->prepare('DELETE FROM inscriptions WHERE evenement_id = ?');
            $stmt->execute([$id]);
            $stmt = $pdo->prepare('DELETE FROM evenements WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::eventDelete(' . $id . ') - ' . $e->getMessage());
            return false;
        }
    }

    protected function eventGetRecommendations(): array
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->query("
                SELECT e.*, c.nom AS categorie_nom,
                       COUNT(DISTINCT i.participant_id) AS inscriptions_count
                FROM evenements e
                LEFT JOIN categories c ON e.categorie_id = c.id
                LEFT JOIN inscriptions i ON e.id = i.evenement_id
                WHERE e.date_evenement >= NOW()
                GROUP BY e.id
                ORDER BY e.date_evenement ASC
            ");
            $evenements = $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::eventGetRecommendations() - ' . $e->getMessage());
            return [];
        }

        foreach ($evenements as &$event) {
            $score = 0.0;
            $now = time();
            $eventTime = strtotime((string)$event['date_evenement']);
            $daysDiff = ($eventTime - $now) / 86400;
            if ($daysDiff >= 0 && $daysDiff <= 30) {
                $score += 30 - $daysDiff;
            }
            if ((float)$event['prix'] === 0.0) {
                $score += 20;
            } elseif ((float)$event['prix'] <= 50) {
                $score += 15;
            } elseif ((float)$event['prix'] <= 100) {
                $score += 10;
            }
            $placesLeft = (int)$event['capacite'] - (int)$event['inscriptions_count'];
            if ($placesLeft > 0) {
                $score += (min($placesLeft, 100) / 100) * 15;
            }
            $score += min((int)$event['inscriptions_count'], 20);
            $event['recommendation_score'] = round($score, 2);
            $event['places_left'] = $placesLeft;
        }
        unset($event);

        usort($evenements, static function ($a, $b) {
            return ($b['recommendation_score'] ?? 0) <=> ($a['recommendation_score'] ?? 0);
        });

        return array_slice($evenements, 0, 6);
    }

    protected function categoryGetAll(): array
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->query('SELECT * FROM categories ORDER BY nom ASC');
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::categoryGetAll() - ' . $e->getMessage());
            return [];
        }
    }

    protected function categoryGetAllPublished(): array
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->query('SELECT * FROM categories WHERE is_published = 1 ORDER BY nom ASC');
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            log_message('WARNING', 'BaseController::categoryGetAllPublished() - ' . $e->getMessage());
            return $this->categoryGetAll();
        }
    }

    protected function categoryGetById(int $id): ?array
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::categoryGetById(' . $id . ') - ' . $e->getMessage());
            return null;
        }
    }

    protected function categoryCreate(string $nom, ?string $description, ?string $imageUrl = null, int $isPublished = 1): bool
    {
        $pdo = get_pdo();
        try {
            $stmt = $pdo->prepare('INSERT INTO categories (nom, description, image_url, is_published) VALUES (?, ?, ?, ?)');
            return $stmt->execute([$nom, $description, $imageUrl, $isPublished]);
        } catch (PDOException $e) {
            try {
                $stmt = $pdo->prepare('INSERT INTO categories (nom, description) VALUES (?, ?)');
                return $stmt->execute([$nom, $description]);
            } catch (PDOException $e2) {
                log_message('ERROR', 'BaseController::categoryCreate() - ' . $e2->getMessage());
                return false;
            }
        }
    }

    protected function categoryUpdate(int $id, string $nom, ?string $description, ?string $imageUrl = null, ?int $isPublished = null): bool
    {
        $pdo = get_pdo();
        try {
            if ($isPublished === null) {
                $stmt = $pdo->prepare('UPDATE categories SET nom = ?, description = ?, image_url = ? WHERE id = ?');
                return $stmt->execute([$nom, $description, $imageUrl, $id]);
            }
            $stmt = $pdo->prepare('UPDATE categories SET nom = ?, description = ?, image_url = ?, is_published = ? WHERE id = ?');
            return $stmt->execute([$nom, $description, $imageUrl, $isPublished, $id]);
        } catch (PDOException $e) {
            try {
                $stmt = $pdo->prepare('UPDATE categories SET nom = ?, description = ? WHERE id = ?');
                return $stmt->execute([$nom, $description, $id]);
            } catch (PDOException $e2) {
                log_message('ERROR', 'BaseController::categoryUpdate() - ' . $e2->getMessage());
                return false;
            }
        }
    }

    protected function categoryDelete(int $id): bool
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::categoryDelete(' . $id . ') - ' . $e->getMessage());
            return false;
        }
    }

    protected function participantGetAll(): array
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->query('SELECT * FROM participants ORDER BY nom ASC, prenom ASC');
            return $this->hydrateParticipantsForDisplay($stmt->fetchAll());
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::participantGetAll() - ' . $e->getMessage());
            return [];
        }
    }

    protected function participantGetById(int $id): ?array
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->prepare('SELECT * FROM participants WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ? $this->hydrateParticipantForDisplay($row) : null;
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::participantGetById(' . $id . ') - ' . $e->getMessage());
            return null;
        }
    }

    protected function participantGetByEmail(string $email): ?array
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->prepare('SELECT * FROM participants WHERE email = ?');
            $stmt->execute([$email]);
            $row = $stmt->fetch();
            return $row ? $this->hydrateParticipantForDisplay($row) : null;
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::participantGetByEmail() - ' . $e->getMessage());
            return null;
        }
    }

    protected function participantCreate(array $data): int
    {
        try {
            $pdo = get_pdo();
            $data = $this->participantApplyImcFromPhysique($data);

            $stmt = $pdo->prepare('SELECT id FROM participants WHERE email = ?');
            $stmt->execute([$data['email']]);
            $existing = $stmt->fetch();
            if ($existing) {
                log_message('INFO', 'Participant déjà existant: ' . $data['email']);
                return (int)$existing['id'];
            }

            $sql = 'INSERT INTO participants (nom, prenom, email, mot_de_passe, telephone, poids, taille, imc, lieu, objectif)
                    VALUES (:nom, :prenom, :email, :mot_de_passe, :telephone, :poids, :taille, :imc, :lieu, :objectif)';
            $stmt = $pdo->prepare($sql);

            $params = array_merge($data, [
                'mot_de_passe' => isset($data['mot_de_passe']) && !str_starts_with((string)$data['mot_de_passe'], '$2y$')
                    ? hash_password((string)$data['mot_de_passe'])
                    : ($data['mot_de_passe'] ?? hash_password('pass123')),
            ]);

            $stmt->execute($params);
            $id = (int)$pdo->lastInsertId();
            log_message('INFO', 'Participant créé avec ID ' . $id);
            return $id;
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::participantCreate() - ' . $e->getMessage());
            return 0;
        }
    }

    protected function participantUpdate(int $id, array $data): bool
    {
        try {
            $pdo = get_pdo();
            $data = $this->participantApplyImcFromPhysique($data);

            $allowed = ['nom', 'prenom', 'email', 'mot_de_passe', 'telephone', 'poids', 'taille', 'imc', 'lieu', 'objectif'];
            $fields = [];
            $params = [];

            foreach ($allowed as $key) {
                if (array_key_exists($key, $data)) {
                    $fields[] = $key . ' = :' . $key;
                    $value = $data[$key];
                    if ($key === 'mot_de_passe' && $value && !str_starts_with((string)$value, '$2y$')) {
                        $value = hash_password((string)$value);
                    }
                    $params[$key] = $value;
                }
            }

            if ($fields === []) {
                return false;
            }

            $params['id'] = $id;
            $sql = 'UPDATE participants SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);
            if ($result) {
                log_message('INFO', 'Participant mis à jour: ' . $id);
            }
            return $result;
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::participantUpdate(' . $id . ') - ' . $e->getMessage());
            return false;
        }
    }

    protected function participantDelete(int $id): bool
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->prepare('DELETE FROM inscriptions WHERE participant_id = ?');
            $stmt->execute([$id]);
            $stmt = $pdo->prepare('DELETE FROM participants WHERE id = ?');
            $result = $stmt->execute([$id]);
            if ($result) {
                log_message('INFO', 'Participant supprimé: ' . $id);
            }
            return $result;
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::participantDelete(' . $id . ') - ' . $e->getMessage());
            return false;
        }
    }

    protected function participantRegisterToEvent(int $participantId, int $evenementId): bool
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->prepare('SELECT id FROM inscriptions WHERE participant_id = ? AND evenement_id = ?');
            $stmt->execute([$participantId, $evenementId]);
            if ($stmt->fetch()) {
                log_message('INFO', 'Participant déjà inscrit à l’événement');
                return true;
            }

            $stmt = $pdo->prepare('INSERT INTO inscriptions (participant_id, evenement_id) VALUES (?, ?)');
            $result = $stmt->execute([$participantId, $evenementId]);
            if ($result) {
                log_message('INFO', 'Participant inscrit à l’événement');
            }
            return $result;
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::participantRegisterToEvent() - ' . $e->getMessage());
            return false;
        }
    }

    protected function participantComputeImcKgCm(float $poidsKg, float $tailleCm): ?float
    {
        if ($poidsKg <= 0 || $tailleCm <= 0) {
            return null;
        }
        $m = $tailleCm / 100.0;
        if ($m <= 0) {
            return null;
        }
        return round($poidsKg / ($m * $m), 2);
    }

    protected function participantResolveImcForDisplay(array $row): ?float
    {
        $poids = (float)($row['poids'] ?? 0);
        $taille = (float)($row['taille'] ?? 0);
        $imcDb = isset($row['imc']) ? (float)$row['imc'] : 0.0;
        if ($imcDb > 0) {
            return $imcDb;
        }
        return $this->participantComputeImcKgCm($poids, $taille);
    }

    protected function participantApplyImcFromPhysique(array $data): array
    {
        $poids = (float)($data['poids'] ?? 0);
        $taille = (float)($data['taille'] ?? 0);
        $imc = (float)($data['imc'] ?? 0);
        if ($imc <= 0 && $poids > 0 && $taille > 0) {
            $computed = $this->participantComputeImcKgCm($poids, $taille);
            if ($computed !== null) {
                $data['imc'] = $computed;
            }
        }
        return $data;
    }

    protected function hydrateParticipantForDisplay(array $participant): array
    {
        $imcDisplay = $this->participantResolveImcForDisplay($participant);
        $participant['imc_display'] = $imcDisplay;
        $participant['imc_is_computed'] = $imcDisplay !== null && ((float)($participant['imc'] ?? 0) <= 0);
        return $participant;
    }

    protected function hydrateParticipantsForDisplay(array $participants): array
    {
        return array_map(fn(array $participant): array => $this->hydrateParticipantForDisplay($participant), $participants);
    }

    protected function recommendationGetAll(): array
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->query('SELECT * FROM recommendations_personnalisees ORDER BY created_at DESC');
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::recommendationGetAll() - ' . $e->getMessage());
            return [];
        }
    }

    protected function recommendationGetById(int $id): ?array
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->prepare('SELECT * FROM recommendations_personnalisees WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::recommendationGetById(' . $id . ') - ' . $e->getMessage());
            return null;
        }
    }

    protected function recommendationCreate(array $data): int
    {
        try {
            $pdo = get_pdo();
            $sql = 'INSERT INTO recommendations_personnalisees (titre, description, categorie_preferee, budget_max, localisation, ai_suggestion, evenements_suggeres)
                    VALUES (:titre, :description, :categorie_preferee, :budget_max, :localisation, :ai_suggestion, :evenements_suggeres)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            $id = (int)$pdo->lastInsertId();
            log_message('INFO', 'Recommandation personnalisée créée avec ID ' . $id);
            return $id;
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::recommendationCreate() - ' . $e->getMessage());
            return 0;
        }
    }

    protected function recommendationDelete(int $id): bool
    {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->prepare('DELETE FROM recommendations_personnalisees WHERE id = ?');
            $result = $stmt->execute([$id]);
            if ($result) {
                log_message('INFO', 'Recommandation personnalisée supprimée: ' . $id);
            }
            return $result;
        } catch (PDOException $e) {
            log_message('ERROR', 'BaseController::recommendationDelete(' . $id . ') - ' . $e->getMessage());
            return false;
        }
    }

    protected function recommendationGenerateAISuggestions(array $userPreferences): array
    {
        try {
            $evenements = $this->eventGetAllPublished();
            $suggestions = [];
            $matchingEvents = [];

            $budgetMax = $userPreferences['budget_max'] ?? null;
            $categoriePreferee = $userPreferences['categorie_preferee'] ?? null;
            $localisation = $userPreferences['localisation'] ?? null;
            $description = strtolower((string)($userPreferences['description'] ?? ''));

            foreach ($evenements as $event) {
                $score = 0;
                $reasons = [];

                if ($categoriePreferee && stripos((string)($event['categorie_nom'] ?? ''), (string)$categoriePreferee) !== false) {
                    $score += 30;
                    $reasons[] = 'Correspond à votre catégorie préférée';
                }

                if ($budgetMax !== null) {
                    $prix = (float)($event['prix'] ?? 0);
                    if ($prix <= $budgetMax) {
                        if ($prix === 0.0) {
                            $score += 25;
                            $reasons[] = 'Événement gratuit';
                        } elseif ($prix <= $budgetMax * 0.5) {
                            $score += 20;
                            $reasons[] = 'Prix abordable';
                        } else {
                            $score += 15;
                            $reasons[] = 'Dans votre budget';
                        }
                    }
                }

                if ($localisation && stripos((string)($event['lieu'] ?? ''), (string)$localisation) !== false) {
                    $score += 20;
                    $reasons[] = 'Lieu correspondant à vos préférences';
                }

                $eventDesc = strtolower((string)($event['description'] ?? ''));
                $eventTitre = strtolower((string)($event['titre'] ?? ''));
                $keywords = explode(' ', $description);
                $keywordMatches = 0;
                foreach ($keywords as $keyword) {
                    if (strlen($keyword) > 2) {
                        if (stripos($eventDesc, $keyword) !== false || stripos($eventTitre, $keyword) !== false) {
                            $keywordMatches++;
                        }
                    }
                }
                if ($keywordMatches > 0) {
                    $score += min($keywordMatches * 5, 15);
                    $reasons[] = 'Correspond à vos intérêts (' . $keywordMatches . ' mots-clés)';
                }

                $capacite = (int)($event['capacite'] ?? 0);
                if ($capacite > 0) {
                    $score += 10;
                    $reasons[] = 'Places disponibles';
                }

                $dateEvent = strtotime((string)($event['date_evenement'] ?? ''));
                $now = time();
                if ($dateEvent > $now) {
                    $daysDiff = ($dateEvent - $now) / 86400;
                    if ($daysDiff <= 30) {
                        $score += max(0, 10 - $daysDiff);
                        $reasons[] = 'Prochainement (' . round($daysDiff) . ' jours)';
                    }
                }

                if ($score > 20) {
                    $matchingEvents[] = [
                        'event' => $event,
                        'score' => $score,
                        'reasons' => $reasons,
                    ];
                }
            }

            usort($matchingEvents, static function ($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            $matchingEvents = array_slice($matchingEvents, 0, 5);

            if ($matchingEvents === []) {
                $suggestions[] = 'Aucun événement ne correspond parfaitement à vos critères. Essayez d’élargir vos préférences.';
            } else {
                $suggestions[] = 'Voici ' . count($matchingEvents) . ' événement(s) qui correspondent à vos préférences :';
                foreach ($matchingEvents as $match) {
                    $event = $match['event'];
                    $suggestions[] = '- ' . $event['titre'] . ' (' . implode(', ', $match['reasons']) . ')';
                }
            }

            return [
                'suggestions' => $suggestions,
                'matching_events' => $matchingEvents,
            ];
        } catch (Exception $e) {
            log_message('ERROR', 'BaseController::recommendationGenerateAISuggestions() - ' . $e->getMessage());
            return [
                'suggestions' => ['Erreur lors de la génération des suggestions.'],
                'matching_events' => [],
            ];
        }
    }
}
