# Changelog

Tous les changements notables de ce plugin sont documentés dans ce fichier.

Le format s'appuie sur [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/),
et ce projet suit le [versionnement sémantique](https://semver.org/lang/fr/).

## [1.3.2] — 2026-05-18

### Ajouté
- Helpers `mask_api_key()` et `redact_keys()` pour masquer les valeurs sensibles.
- Flux dédiés `admin-post.php` pour le remplacement et la suppression de la clé API Google (nonce + `manage_options`).

### Modifié
- Refonte complète de la page de réglages avec le design system (header sombre, onglets, cards, sidebar) — préfixe CSS `def-`.
- Aperçu de la clé Google désormais masqué uniquement, jamais exposé en clair.
- Stockage de la clé API en `autoload=no`.
- Assets admin chargés uniquement sur la page du plugin.

## [1.3.1] — 2025-12-02

### Modifié
- Bump de version.
- Révision du README pour plus de clarté.

## [1.3.0] — 2025-08-20

### Ajouté
- Possibilité d'activer/désactiver les widgets depuis la page de réglages.
- Liste des widgets gérée via `includes/widget-list.php`.

### Modifié
- Ajustement du padding des icônes.

## [1.2.0] — 2025-06-04

### Ajouté
- Nouveau champ **Select Post** (`includes/fields/select-posts.php`).

### Corrigé
- Correctif du padding des icônes.

## [1.1.2] — 2025-04-25

### Ajouté
- Regroupement des menus d'administration.

### Corrigé
- Correctifs sur le mécanisme de mise à jour (`update-plugin.php`).

## [1.1.0] — 2025-04-11

### Ajouté
- Champ **City Autocomplete** avec autocomplétion via l'API Google Places.
- Champ **Select2** étendu pour Elementor.
- Extension d'icônes personnalisées (`includes/extensions/icons.php`).
- Helper utilitaire (`includes/helper.php`).
- Mécanisme de mise à jour automatique du plugin via GitHub (`includes/update-plugin.php`).
- Assets front : `icons-form.css`, `icons-form.js`, `city-autocomplete.js`.

### Supprimé
- Ancien répertoire `includes/old/` (nettoyage).

## [1.0.0] — 2025-04-11

### Ajouté
- Version initiale du plugin **DC Elementor Fields**.
