# DC Elementor Fields

Ajoute de nouveaux types de champs personnalisés dans le widget Form d'Elementor Pro.

![Version](https://img.shields.io/badge/version-1.3.1-blue)
![WordPress](https://img.shields.io/badge/WordPress-6.7%2B-green)
![Elementor Pro](https://img.shields.io/badge/Elementor%20Pro-Required-orange)
![Downloads](https://img.shields.io/github/downloads/dynamiccreative/dc-elementor-fields/total)
![Downloads@latest](https://img.shields.io/github/downloads/dynamiccreative/dc-elementor-fields/latest/total)

## 📋 Description

DC Elementor Fields est un plugin WordPress qui étend les fonctionnalités du widget Form d'Elementor Pro en ajoutant des champs personnalisés avancés et des extensions visuelles. Ce plugin est idéal pour créer des formulaires plus interactifs et professionnels.

## ✨ Fonctionnalités

### Champs de formulaire

| Champ | Description |
|-------|-------------|
| **Google City Autocomplete** | Champ texte avec autocomplétion des villes via Google Places API |
| **Select2** | Champ select amélioré avec recherche, sélection multiple et style personnalisable |
| **Select Posts (CPT)** | Liste déroulante dynamique des articles ou Custom Post Types |

### Extensions

| Extension | Description |
|-----------|-------------|
| **Icons Pre/Post Field** | Ajoute des icônes avant ou après les champs de formulaire |

## 📦 Installation

### Installation manuelle

1. Téléchargez la dernière release depuis [GitHub](https://github.com/dynamiccreative/dc-elementor-fields/releases)
2. Dans WordPress, allez dans **Extensions > Ajouter**
3. Cliquez sur **Téléverser une extension**
4. Sélectionnez le fichier ZIP téléchargé
5. Cliquez sur **Installer maintenant** puis **Activer**

### Via Git

```bash
cd wp-content/plugins/
git clone https://github.com/dynamiccreative/dc-elementor-fields.git
```

## ⚙️ Configuration

### Accès aux paramètres

Après activation, accédez aux paramètres via :
- **Réglages > DC Elementor Fields** (installation standard)
- **DC Settings > Elementor Fields** (si DC Support Technique est installé)

### Paramètres Google API

Pour utiliser le champ **City Autocomplete**, vous devez :

1. Obtenir une clé API Google Cloud avec les APIs suivantes activées :
   - Maps JavaScript API
   - Places API
   - Geometry Library

2. Entrer la clé API dans les paramètres du plugin

3. (Optionnel) Définir une restriction par pays :
   - France (fr)
   - États-Unis (us)
   - Royaume-Uni (gb)
   - Canada (ca)
   - Allemagne (de)
   - Tous les pays (all)

### Activation des champs et extensions

Chaque champ et extension peut être activé/désactivé individuellement depuis la page de paramètres.

## 📖 Documentation des champs

### Google City Autocomplete

Champ texte avec autocomplétion des noms de villes utilisant l'API Google Places.

**Utilisation :**
1. Ajoutez un widget Form dans Elementor
2. Ajoutez un nouveau champ de type "Google City Autocomplete"
3. Configurez le placeholder et les options de validation

**Options disponibles :**
- Placeholder personnalisé
- Champ requis (validation)

---

### Select2 Personnalisé

Champ select amélioré avec la librairie Select2.

**Utilisation :**
1. Ajoutez un champ de type "Select2 Personnalisé"
2. Définissez les options au format `valeur|label` (une par ligne)
3. Activez la sélection multiple si nécessaire

**Options disponibles :**
- Options personnalisées (format : `value|Label`)
- Sélection multiple
- Placeholder personnalisé
- Styles héritant des paramètres Elementor (couleurs, typographie, bordures, etc.)

**Exemple d'options :**
```
option1|Première option
option2|Deuxième option
option3|Troisième option
```

---

### Select Posts (CPT)

Liste déroulante affichant dynamiquement les articles d'un type de contenu.

**Utilisation :**
1. Ajoutez un champ de type "Select Posts"
2. Sélectionnez le Custom Post Type souhaité
3. (Optionnel) Définissez une option pré-sélectionnée par son slug

**Options disponibles :**
- Sélection du Custom Post Type (Post, ou tout CPT public)
- Option pré-sélectionnée (par slug)
- Label du champ
- Champ requis

## 🎨 Documentation des extensions

### Icons Pre/Post Field

Permet d'ajouter des icônes aux champs de formulaire (sur le label ou dans le champ input).

**Champs compatibles :**
- Date, Time
- Tel, Text, Email
- Textarea, Number, URL
- Password
- Select, Select Posts
- Google City Autocomplete

**Configuration par champ :**
1. Éditez un champ de formulaire
2. Dans l'onglet "Enchanted", choisissez la position de l'icône :
   - **No Icon** : Pas d'icône
   - **On Label** : Icône sur le label
   - **On Input** : Icône dans le champ input
3. Sélectionnez l'icône souhaitée

**Styles globaux (Section "Icons" dans l'onglet Style) :**
- Couleur de l'icône sur les labels
- Couleur de l'icône sur les inputs
- Taille des icônes (labels et inputs)
- Position de l'icône (gauche/droite)

## 🔄 Mises à jour automatiques

Le plugin intègre un système de mise à jour automatique depuis GitHub. Les nouvelles versions sont détectées et peuvent être installées directement depuis l'interface WordPress.

## 📁 Structure du projet

```
dc-elementor-fields/
├── dc-elementor-fields.php     # Fichier principal du plugin
├── includes/
│   ├── helper.php              # Fonctions utilitaires
│   ├── widget-list.php         # Liste des widgets/extensions
│   ├── GitHubUpdater.php       # Système de mise à jour GitHub
│   ├── fields/
│   │   ├── city-autocomplete-field.php
│   │   ├── select2.php
│   │   └── select-posts.php
│   └── extensions/
│       └── icons.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── icons-form.css
│   └── js/
│       ├── city-autocomplete.js
│       ├── icons-form.js
│       └── icons-form.min.js
└── README.md
```

## 🔧 Prérequis

- **WordPress** : 6.7 ou supérieur
- **PHP** : 7.4 ou supérieur
- **Elementor Pro** : Requis (le widget Form est une fonctionnalité Pro)
- **Clé API Google** : Requise pour le champ City Autocomplete

## 🐛 Dépannage

### Le champ City Autocomplete ne fonctionne pas

1. Vérifiez que la clé API Google est correctement configurée
2. Assurez-vous que les APIs Maps JavaScript et Places sont activées
3. Vérifiez les restrictions de domaine sur votre clé API
4. Consultez la console du navigateur pour les erreurs

### Les styles Select2 ne s'appliquent pas

1. Videz le cache de votre site
2. Régénérez les fichiers CSS d'Elementor (Elementor > Outils > Régénérer les CSS)
3. Vérifiez qu'aucun autre plugin n'entre en conflit avec Select2

### Les icônes ne s'affichent pas

1. Assurez-vous que l'extension "Icons Pre/Post Field" est activée dans les paramètres
2. Vérifiez que le type de champ est compatible avec les icônes
3. Rafraîchissez l'éditeur Elementor

## 📝 Changelog

### Version 1.3.0
- Ajout du champ Select Posts (CPT)
- Amélioration des styles Select2
- Compatibilité WordPress 6.8

### Version 1.2.0
- Ajout de l'extension Icons Pre/Post Field
- Amélioration de l'intégration Select2

### Version 1.1.0
- Ajout du champ Select2 personnalisé
- Restriction par pays pour City Autocomplete

### Version 1.0.0
- Version initiale
- Champ Google City Autocomplete

## 🤝 Contribution

Les contributions sont les bienvenues ! 

1. Forkez le projet
2. Créez votre branche (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Committez vos changements (`git commit -m 'Ajout nouvelle fonctionnalité'`)
4. Pushez vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est sous licence GPL v2 ou ultérieure.

## 👥 Auteur

**[Dynamic Creative](https://dynamic-creative.com)** - Agence digitale / Infogérance

Développé par [@teknopop-dc](https://github.com/teknopop-dc)

## 🔗 Liens utiles

- [Site web](https://dynamic-creative.com)
- [GitHub Repository](https://github.com/dynamiccreative/dc-elementor-fields)
- [Signaler un bug](https://github.com/dynamiccreative/dc-elementor-fields/issues)
- [Documentation Google Places API](https://developers.google.com/maps/documentation/places/web-service)
- [Documentation Select2](https://select2.org/)
