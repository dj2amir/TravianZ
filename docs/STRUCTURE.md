# TravianZ - Project Structure Analysis

## Overview

**TravianZ** is an open-source PHP-based browser strategy game — a clone of the popular game **Travian**. It is a massively multiplayer online game where players build villages, train armies, forge alliances, and compete for world domination through the construction of World Wonders (WW).

- **Repository**: https://github.com/Shadowss/TravianZ
- **Website**: https://travianz.org
- **Language**: PHP (procedural + OOP hybrid), SQL (MySQL/MariaDB), JavaScript, HTML/CSS
- **License**: Proprietary (TravianZ Project)
- **First Release**: ~2010
- **Latest Update**: 2026 (active development)

---

## Directory Tree & Purpose

```
TravianZ/
├── index.php                    # Landing page / server selection
├── login.php                    # Login page
├── anmelden.php                 # Registration page
├── dorf1.php                    # Village view 1 (resource fields)
├── dorf2.php                    # Village view 2 (buildings in village center)
├── dorf3.php                    # Village view 3 (T4 style overview)
├── build.php                    # Building actions router
├── karte.php / karte2.php       # World map
├── berichte.php                 # Battle / adventure reports
├── nachrichten.php              # Messages / Inbox
├── allianz.php                  # Alliance management
├── statistiken.php              # Rankings & statistics
├── spieler.php                  # Player profile
├── tutorial.php                 # In-game tutorial
├── plus.php / plus1.php         # Travian Plus (premium) features
├── packages.php                 # Gold packages / marketplace
├── festival.php                 # Festival celebration
├── warsim.php                   # Battle simulator
├── build_croppers.php           # Crop finder utility
├── crop_finder.php              # Crop finder page
├── cron.php                     # Automation runner (cron job endpoint)
├── ajax.php                     # AJAX handler
├── a2b.php / a2b2.php           # Troop movement interface
├── support.php                  # Reinforcement management
├── activate.php                 # Account activation
├── banned.php                   # Ban notification page
├── celebration.php              # Village celebration
├── logout.php                   # Logout handler
├── manual.php                   # Game manual
├── maintenance.php              # Maintenance mode page
├── mailme.php                   # Password recovery
├── notification/                # T4 notification system
├── password.php                 # Password change
├── rules.php                    # Game rules
├── spielregeln.php              # Game rules (DE)
├── agb.php                      # Terms & Conditions
├── impressum.php                # Imprint / Legal
├── terms.php                    # Terms of service
├── version.php                  # Version info
├── winner.php                   # Winner page (after WW completion)
├── autoloader.php               # PSR-4-like autoloader for App\ namespace
│
├── GameEngine/                  # **CORE GAME ENGINE** (main logic)
│   ├── config.php               # Runtime configuration (generated during install)
│   ├── Database.php             # MYSQLi_DB class (main DB class with traits)
│   ├── Village.php              # Village management logic
│   ├── Building.php             # Building construction/upgrade logic
│   ├── Units.php                # Unit (troop) management
│   ├── Battle.php               # Battle resolution engine
│   ├── Technology.php           # Tech tree / research
│   ├── Account.php              # Registration, login, logout
│   ├── Alliance.php             # Alliance management
│   ├── Market.php               # Marketplace / trading
│   ├── Message.php              # In-game messaging
│   ├── Automation.php           # Cron automation (tick-based game loop)
│   ├── Generator.php            # World data generation & utilities
│   ├── Ranking.php              # Rankings calculation
│   ├── Session.php              # Session management
│   ├── Form.php                 # Form validation helper
│   ├── BBCode.php               # Text formatting
│   ├── Chat.php                 # Alliance chat
│   ├── Mailer.php               # Email sending
│   ├── Protection.php           # Beginner protection system
│   ├── PushProtection.php       # Anti-push (resource transfer abuse) detection
│   ├── MultiAccount.php         # Multi-account detection engine
│   ├── RegBlock.php             # Registration blocklist
│   ├── GoldShop.php             # Gold shop / premium currency
│   ├── HeroItems.php            # T4 Hero items/inventory
│   ├── HeroAdventure.php        # T4 Hero adventures
│   ├── HeroAuction.php          # T4 Hero item auction house
│   ├── HeroBattleBonus.php      # Hero battle bonuses
│   ├── Artifacts.php            # Artifact system
│   ├── AllianceBonus.php        # T4 Alliance bonuses
│   ├── Heatmap.php              # Map heatmap data
│   ├── Profile.php              # Player profile management
│   ├── QuestConfig.php          # Quest configuration
│   ├── Logging.php              # Action logging system
│   ├── Multisort.php            # Array sorting utility
│   ├── functions.php            # Helper functions (rc_tok, etc.)
│   │
│   ├── Database/                # **Phase S1: DB class split into traits**
│   │   ├── DatabaseConnectionCore.php
│   │   ├── DatabaseUserQueries.php
│   │   ├── DatabaseVillageQueries.php
│   │   ├── DatabaseForumQueries.php
│   │   ├── DatabaseAllianceQueries.php
│   │   ├── DatabaseMessageQueries.php
│   │   ├── DatabaseMarketQueries.php
│   │   ├── DatabaseBuildingQueries.php
│   │   ├── DatabaseTroopQueries.php
│   │   ├── DatabaseMovementQueries.php
│   │   ├── DatabaseHeroQueries.php
│   │   ├── DatabaseStatisticsQueries.php
│   │   ├── DatabaseArtefactQueries.php
│   │   └── DatabaseSystemQueries.php
│   │
│   ├── Automation/              # **Phase S2: Automation class split into traits**
│   │   ├── AutomationVillageUpkeep.php
│   │   ├── AutomationAccountMaintenance.php
│   │   ├── AutomationBuildQueue.php
│   │   ├── AutomationMarket.php
│   │   ├── AutomationBattleResolution.php
│   │   ├── AutomationTroopMovements.php
│   │   ├── AutomationTraining.php
│   │   ├── AutomationHero.php
│   │   ├── AutomationStarvation.php
│   │   ├── AutomationNatarsWW.php
│   │   ├── AutomationMedals.php
│   │   ├── AutomationCleanup.php
│   │   └── AutomationPlayerStatistics.php
│   │
│   ├── Data/                    # Static game data (arrays/definitions)
│   │   ├── buidata.php          # Building definitions & costs
│   │   ├── unitdata.php         # Unit definitions & stats
│   │   ├── hero_full.php        # Hero definitions
│   │   ├── hero_items.php       # T4 Hero item catalog
│   │   ├── resdata.php          # Resource field definitions
│   │   ├── cp.php               # Culture points data
│   │   ├── cel.php              # Celebration data
│   │   └── festival.php         # Festival data
│   │
│   ├── Lang/                    # Language files (translation)
│   │   ├── en.php, zh.php, fr.php, ro.php, it.php
│   │
│   ├── Admin/                   # Admin panel engine
│   │   ├── admin.php            # Admin panel entry
│   │   ├── function.php         # Admin helper functions
│   │   ├── database.php         # Admin DB operations (install/mass actions)
│   │   ├── csrf.php             # CSRF protection
│   │   └── Mods/                # ~40 admin action modules
│   │
│   └── Game/                    # Sub-game mechanics
│       └── WorldWonderName.php  # WW name generation
│
├── Admin/                       # Admin panel templates & assets
│   ├── index.php, admin.php
│   ├── ajax.js, jquery.cookie.js
│   ├── Templates/               # ~80 admin page templates (.tpl)
│   └── img/                     # Admin images
│
├── Templates/                   # Frontend templates (.tpl files, ~200+)
│   ├── Build/                   # Building-specific templates (50+)
│   ├── Map/                     # Map-related templates
│   ├── Alliance/                # Alliance pages
│   ├── Notice/                  # Notification templates
│   ├── Message/                 # Message templates
│   ├── Ranking/                 # Ranking templates
│   ├── Profile/                 # Profile pages
│   ├── Plus/                    # Travian Plus pages
│   ├── Manual/                  # Manual pages (150+)
│   ├── Tutorial/                # Tutorial pages
│   ├── goldClub/                # Gold Club (farm lists)
│   ├── a2b/                     # Troop movement templates
│   ├── Ajax/                    # AJAX partial templates
│   ├── Simulator/               # Battle simulator templates
│   ├── Anleitung/               # Game guide templates
│   ├── dorf3/                   # T4 village overview
│   └── [root templates]         # header, footer, menu, etc.
│
├── src/                         # Modern PHP (namespaced, OOP)
│   ├── Database/
│   │   └── IDbConnection.php    # DB connection interface
│   ├── Entity/
│   │   └── User.php             # User entity class
│   ├── Game/
│   └── Utils/
│       ├── AccessLogger.php     # Request logging
│       ├── DateTime.php         # Date/time utilities
│       ├── DebugErrorLogger.php # Error logging
│       ├── IpResolver.php       # Client IP detection (proxy-aware)
│       └── Math.php             # Math utilities (isInt, isFloat)
│
├── Security/
│   └── Security.class.php       # Input sanitization / security layer
│
├── install/                     # Web-based installer
│   ├── index.php                # Install wizard UI
│   ├── process.php              # Install form handling
│   ├── data/constant_format.tpl # Config template
│   ├── include/accounts.php     # Default accounts
│   └── templates/               # Install wizard templates
│
├── var/                         # Runtime data
│   ├── installed                # Marker file (created after installation)
│   ├── db/
│   │   ├── struct.sql           # Database schema (all tables)
│   │   ├── datagen-world-data.sql # World map generation
│   │   └── datagen-oasis-troops-regen.sql # Oasis troop regeneration
│   ├── log/                     # Log files directory
│   └── tools/
│       └── generate_hero_sprites.php
│
├── docker/                      # Docker configuration
│   └── php/zz-travianz.ini      # PHP custom config
├── docker-compose.yml           # Docker Compose (web + db + phpmyadmin)
├── Dockerfile                   # PHP Apache image
├── gpack/                       # Graphics packs (CSS, images)
│   ├── travian/                 # Main theme
│   ├── travian_default/         # Default theme
│   └── travian_t4/              # T4 theme
├── img/                         # Game images & sprites
├── css/                         # Additional CSS
├── js/ (root .js files)         # JavaScript
│   ├── mt-core.js               # MooTools core (~125KB)
│   ├── mt-full.js               # MooTools full
│   ├── mt-more.js               # MooTools more
│   ├── new.js                   # Main game JS
│   ├── new2.js                  # Additional game JS
│   ├── unx.js                   # Utility JS
│   ├── uncrypt.js               # Uncrypted helpers
│   └── flaggen.js               # Flag language selector
│
└── docs/                        # Project documentation (our analysis)
    ├── STRUCTURE.md             # This file
    ├── ARCHITECTURE.md          # Architectural analysis
    └── PROBLEMS.md              # Identified issues & problems
```

---

## Architecture Layers

### 1. Presentation Layer (Frontend)
- **Templates**: `.tpl` files with embedded PHP — no templating engine
- **JavaScript**: MooTools 1.x framework (~2008) with custom game scripts
- **CSS**: T4-inspired travian theme, graphics packs

### 2. Application Layer
- **Route Controllers**: Root-level PHP files (`dorf1.php`, `build.php`, etc.) acting as page controllers
- **Game Logic**: `GameEngine/` classes handling all game mechanics
- **Account System**: Registration, login, logout, activation, email verification

### 3. Automation Layer
- **Cron-based Game Loop**: `Automation.php` + 13 traits handling ticks every 60-300 seconds
- **Processes**: Resource production, building queues, troop movements, battle resolution, hero updates, medal awarding, cleanup

### 4. Data Layer
- **Database**: MySQL/MariaDB, single `MYSQLi_DB` class (refactored into 14 traits in Phase S1)
- **Schema**: ~45 tables covering all game entities
- **Caching**: In-request static caching (arrays within the DB class), no external cache

### 5. Infrastructure Layer
- **Web Server**: Apache (via Docker)
- **Docker**: 3-container setup (web, db, phpmyadmin)
- **Security**: Custom `Security.class.php` for input sanitization
- **Autoloader**: Custom autoloader for `App\` namespace, manual includes for legacy code
