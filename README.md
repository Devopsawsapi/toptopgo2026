# TopTopGo Backend API

Backend Laravel pour l'application de covoiturage TopTopGo, adaptée au Congo Brazzaville.

## Stack Technique

- **Framework**: Laravel 11
- **PHP**: 8.2+
- **Base de données**: PostgreSQL
- **Cache**: Redis
- **Queue**: Laravel Queues (Redis)
- **Auth**: Laravel Sanctum (JWT)
- **Real-time**: Pusher/WebSockets

## Intégrations de Paiement

### 1. Peex (Principal)
- Documentation: https://peex-api-docs.peexit.com/
- Opérateurs supportés: MTN, Airtel
- Services: Collection, Payout, Bank Transfer

### 2. MTN Mobile Money
- Collection (Request to Pay)
- Disbursement (Driver Payout)

### 3. Airtel Money
- USSD Push Payment
- B2C Disbursement

### 4. Stripe
- Paiement par carte internationale
- Escrow (capture manuelle)
- Stripe Connect pour les chauffeurs

## Installation

```bash
# Cloner le repo
git clone https://github.com/Devopsawsapi/Backendtoptopgo.git
cd Backendtoptopgo

# Installer les dépendances
composer install

# Configurer l'environnement
cp .env.example .env
php artisan key:generate

# Configurer la base de données dans .env puis:
php artisan migrate

# Démarrer le serveur
php artisan serve
```

## Configuration des Paiements

### Peex
```env
PEEX_SANDBOX=true
PEEX_SECRET_KEY=votre-cle-secrete
```

### MTN MoMo
```env
MTN_MOMO_SUBSCRIPTION_KEY=votre-subscription-key
MTN_MOMO_API_USER=votre-api-user
MTN_MOMO_API_KEY=votre-api-key
```

### Airtel Money
```env
AIRTEL_MONEY_CLIENT_ID=votre-client-id
AIRTEL_MONEY_CLIENT_SECRET=votre-client-secret
```

### Stripe
```env
STRIPE_SECRET_KEY=sk_test_xxx
STRIPE_PUBLISHABLE_KEY=pk_test_xxx
```

## API Endpoints

### Authentification
```
POST /api/auth/register      - Inscription
POST /api/auth/login         - Connexion
POST /api/auth/verify-phone  - Vérification OTP
```

### Paiements
```
GET  /api/payments/methods           - Méthodes disponibles
POST /api/payments/rides/{id}/pay    - Payer un trajet
GET  /api/payments/status/{ref}      - Statut transaction
GET  /api/payments/wallet            - Solde wallet (chauffeur)
POST /api/payments/withdraw          - Retrait (chauffeur)
GET  /api/payments/transactions      - Historique
```

### Trajets
```
POST /api/rides/estimate     - Estimer le prix
POST /api/rides              - Créer un trajet
GET  /api/rides/active       - Trajet en cours
POST /api/rides/{id}/cancel  - Annuler
POST /api/rides/{id}/rate    - Noter
```

### Chauffeurs
```
POST /api/driver/go-online       - Se connecter
POST /api/driver/go-offline      - Se déconnecter
POST /api/driver/update-location - MAJ position
POST /api/driver/rides/{id}/accept   - Accepter course
POST /api/driver/rides/{id}/start    - Démarrer course
POST /api/driver/rides/{id}/complete - Terminer course
```

## Webhooks

Les webhooks sont configurés pour recevoir les notifications des providers:

```
POST /api/webhooks/peex/collect      - Peex (paiement reçu)
POST /api/webhooks/peex/payout       - Peex (paiement chauffeur)
POST /api/webhooks/mtn-momo          - MTN MoMo
POST /api/webhooks/airtel-money      - Airtel Money
POST /api/webhooks/stripe            - Stripe
```

## Flux de Paiement

### 1. Paiement Passager (Escrow)
```
Passager demande trajet
    |
Estimation du prix
    |
Initiation paiement (Peex/MTN/Airtel/Stripe)
    |
Paiement sequestre (escrow)
    |
Chauffeur accepte et effectue le trajet
    |
Trajet termine
    |
Liberation du paiement au chauffeur (moins commission)
```

### 2. Retrait Chauffeur
```
Chauffeur demande retrait
    |
Verification solde wallet
    |
Initiation payout (Peex/MTN/Airtel)
    |
Confirmation via webhook
    |
Mise a jour wallet
```

## Structure des Fichiers

```
app/
|-- Http/Controllers/Api/
|   |-- PaymentController.php
|   |-- WebhookController.php
|   |-- ...
|-- Models/
|   |-- User.php
|   |-- Ride.php
|   |-- Transaction.php
|   |-- Wallet.php
|   |-- ...
|-- Services/Payment/
|   |-- PaymentProviderInterface.php
|   |-- PaymentService.php
|   |-- PeexService.php
|   |-- MtnMomoService.php
|   |-- AirtelMoneyService.php
|   |-- StripeService.php
|-- Events/
    |-- PaymentCompleted.php
    |-- PaymentFailed.php
    |-- PayoutCompleted.php
```

## Commission Plateforme

La commission par defaut est de **15%** avec un minimum de **100 XAF**.

```php
// Exemple pour un trajet a 5000 XAF
Prix trajet: 5000 XAF
Commission (15%): 750 XAF
Gains chauffeur: 4250 XAF
```

## Tests

```bash
php artisan test
```

## Licence

Proprietaire - TopTopGo
