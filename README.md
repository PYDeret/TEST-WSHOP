# API REST — Gestion de Magasins

API REST PHP 8.2 sans framework pour la gestion de magasins (CRUD), avec authentification JWT, cache Redis et Docker.

---

## Prérequis

- [Docker](https://docs.docker.com/get-docker/) + [Docker Compose](https://docs.docker.com/compose/)
- [Postman](https://www.postman.com/downloads/) — **obligatoire** pour tester l'API (la collection et l'environnement sont fournis dans `postman/`)

---

## Lancement

```bash
# 1. Cloner / extraire le projet
cd wshop-api-rest-test

# 2. Copier le fichier d'environnement
cp .env.example .env

# 3. Démarrer les conteneurs
docker-compose up -d --build

# 4. Installer les dépendances
docker-compose exec php composer install

# 5. Exécuter les migrations
docker-compose exec php php bin/migrate.php
```

L'API est disponible sur **http://localhost:8080**.

> **Note :** La racine `/` ne répond à aucune route. Tous les endpoints sont préfixés par `/api/`.

---

## Prise en main rapide avec Postman

> **Postman est requis** pour tester cette API. Les fichiers de collection et d'environnement sont fournis dans le dossier `postman/`.

### 1. Importer la collection et l'environnement

1. Ouvrir Postman
2. **File → Import** (ou glisser-déposer) les deux fichiers :
   - `postman/wshop-api.postman_collection.json`
   - `postman/wshop-api.postman_environment.json`
3. En haut à droite, sélectionner l'environnement **wshop-api — local**

### 2. S'authentifier

1. Ouvrir le dossier **Auth** dans la collection
2. Exécuter **Register** (ou **Login** si le compte existe déjà)
3. Le token JWT est automatiquement sauvegardé dans la variable `{{token}}` — aucune action manuelle nécessaire

### 3. Utiliser les endpoints

Toutes les requêtes du dossier **Stores** utilisent `{{token}}` et `{{base_url}}` automatiquement.

La requête **Créer un magasin** sauvegarde l'ID retourné dans `{{store_id}}`, qui est ensuite utilisé par les requêtes GET, PUT, PATCH et DELETE.

### Variables d'environnement

| Variable   | Valeur par défaut        | Rôle                                    |
|------------|--------------------------|-----------------------------------------|
| `base_url` | `http://localhost:8080`  | URL de base de l'API                    |
| `token`    | *(vide, auto-rempli)*    | Token JWT — alimenté par Register/Login |
| `store_id` | `1`                      | ID du magasin — alimenté par POST store |

---

## Authentification

Tous les endpoints nécessitent un token JWT via le header :
```
Authorization: Bearer <token>
```

### S'enregistrer
```http
POST /api/auth/register
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "monmotdepasse"
}
```

**Règles :** `email` valide requis · `password` minimum 8 caractères

### Se connecter
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "monmotdepasse"
}
```

**Réponse :**
```json
{
  "data": {
    "token": "eyJ...",
    "token_type": "Bearer",
    "expires_in": 86400
  }
}
```

---

## Endpoints Magasins

### Lister les magasins
```http
GET /api/stores
Authorization: Bearer <token>
```

**Paramètres de requête (tous optionnels) :**

| Paramètre  | Description                                      | Exemple              |
|------------|--------------------------------------------------|----------------------|
| `city`     | Filtre par ville (exact)                         | `?city=Paris`        |
| `country`  | Filtre par pays                                  | `?country=FR`        |
| `category` | Filtre par catégorie                             | `?category=clothing` |
| `is_active`| Filtre par statut actif (0 ou 1)                 | `?is_active=1`       |
| `name`     | Recherche partielle sur le nom                   | `?name=zara`         |
| `sort`     | Champ de tri (id, name, city, created_at, …)     | `?sort=name`         |
| `order`    | Sens du tri (`asc` ou `desc`)                    | `?order=asc`         |
| `page`     | Numéro de page (défaut : 1)                      | `?page=2`            |
| `per_page` | Résultats par page (défaut : 20, max : 100)      | `?per_page=10`       |

**Exemple combiné :**
```
GET /api/stores?city=Paris&sort=name&order=asc&page=1&per_page=10
```

### Récupérer un magasin
```http
GET /api/stores/{id}
Authorization: Bearer <token>
```

### Créer un magasin
```http
POST /api/stores
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "Ma Boutique",
  "address": "10 rue de la Paix",
  "city": "Paris",
  "postal_code": "75001",
  "country": "FR",
  "phone": "0123456789",
  "email": "contact@maboutique.fr",
  "category": "clothing",
  "is_active": true
}
```

**Champs requis :** `name`, `address`, `city`, `postal_code`

### Mettre à jour un magasin (remplacement complet)
```http
PUT /api/stores/{id}
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "Nouveau Nom",
  "address": "5 avenue des Champs",
  "city": "Paris",
  "postal_code": "75008"
}
```

### Mise à jour partielle
```http
PATCH /api/stores/{id}
Authorization: Bearer <token>
Content-Type: application/json

{
  "city": "Lyon",
  "is_active": false
}
```

### Supprimer un magasin
```http
DELETE /api/stores/{id}
Authorization: Bearer <token>
```

---

## Modèle Store

| Champ        | Type    | Requis | Description                      |
|--------------|---------|--------|----------------------------------|
| id           | integer | —      | Identifiant auto-généré          |
| name         | string  | Oui    | Nom du magasin (max 150)         |
| address      | string  | Oui    | Adresse (max 255)                |
| city         | string  | Oui    | Ville (max 100)                  |
| postal_code  | string  | Oui    | Code postal (max 20)             |
| country      | string  | Non    | Pays (défaut: FR, max 100)       |
| phone        | string  | Non    | Téléphone (max 30)               |
| email        | string  | Non    | Email (max 150, validé)          |
| category     | string  | Non    | Catégorie (max 80)               |
| is_active    | boolean | Non    | Actif (défaut: true)             |
| created_at   | string  | —      | Date de création (ISO 8601)      |
| updated_at   | string  | —      | Date de mise à jour (ISO 8601)   |

---

## Format des réponses

**Succès liste :**
```json
{
  "data": [ { "id": 1, "name": "...", ... } ],
  "meta": {
    "total": 42,
    "page": 1,
    "per_page": 20,
    "pages": 3
  }
}
```

**Succès ressource unique :**
```json
{
  "data": { "id": 1, "name": "...", ... }
}
```

**Erreur validation (422) :**
```json
{
  "error": "Validation failed",
  "errors": {
    "name": "The name field is required.",
    "email": "The email field must be a valid email address."
  }
}
```

**Codes HTTP :**
| Code | Signification            |
|------|--------------------------|
| 200  | OK                       |
| 201  | Créé                     |
| 204  | Supprimé (pas de corps)  |
| 401  | Non authentifié          |
| 404  | Ressource introuvable    |
| 409  | Conflit (email dupliqué) |
| 422  | Erreur de validation     |
| 500  | Erreur serveur           |

---

## Tests & qualité de code

```bash
# Lancer la suite QA complète (PHPStan + CS Fixer check + PHPUnit)
docker-compose exec php composer qa

# Ou chaque outil séparément
docker-compose exec php composer phpstan # Analyse statique (niveau 6)
docker-compose exec php composer cs-check  # Vérification du style (dry-run, aucune écriture)
docker-compose exec php composer cs-fix # Correction automatique du style
docker-compose exec php composer test # Tests unitaires et d'intégration
```

---

## Architecture

```
public/index.php          Front controller — bootstrap + dispatch + gestion d'erreurs
src/
├── Bootstrap/
│   ├── Container.php     Conteneur DI — instanciation lazy (??=) de tous les services
│   └── Routes.php        Enregistrement des routes, séparé en méthodes privées par groupe
├── Cache/
│   ├── CacheInterface    Contrat cache (get / set / delete / deleteByPattern)
│   ├── RedisCache        Implémentation Redis (TTL : 60 s liste, 300 s ressource)
│   └── NullCache         Fallback silencieux si Redis indisponible
├── Controllers/
│   ├── AbstractController  parseBody() partagée
│   ├── AuthController      POST /auth/register · /auth/login
│   └── StoreController     findAll · findById · post · put · patch · delete
├── Database/
│   └── Database          Singleton PDO (setInstance() pour les tests)
├── Exceptions/           HttpException · NotFoundException · ValidationException · UnauthorizedException
├── Http/
│   ├── RequestContext    Porteur du payload JWT pour la durée de la requête
│   └── Response          Helper json() statique
├── Middleware/
│   └── AuthMiddleware    Extrait et valide le Bearer token → RequestContext
├── Models/
│   └── Store             readonly class PHP 8.2 — Value Object immuable
├── Repositories/
│   ├── Contracts/StoreRepositoryInterface
│   └── StoreRepository   PDO — filtres, tri, pagination, CRUD
├── Router/
│   └── Router            Routeur regex avec support middleware chaîné
├── Serializers/
│   └── StoreSerializer   Contrôle précis des champs exposés en JSON
├── Services/
│   ├── AuthService       Inscription / connexion / vérification JWT
│   └── StoreService      Logique métier + invalidation cache
└── Validators/
    └── StoreValidator    Validation create / update / patch avec messages d'erreur typés
```

**Principes appliqués :** 
- Repository pattern 
- DIP (dépendances sur interfaces) 
- SRP (une responsabilité par classe) 
- Value Objects immutables (`readonly class`) 
- Injection de dépendances par constructeur.

---

## Commandes utiles

```bash
# Voir les logs PHP
docker-compose logs -f php

# Accéder au conteneur PHP
docker-compose exec php sh

# Arrêter les services
docker-compose down

# Supprimer aussi les volumes (reset BDD)
docker-compose down -v
```
