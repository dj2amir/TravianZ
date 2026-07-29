# TravianZ - Architecture Analysis

## Technology Stack

| Layer | Technology | Version/Details |
|-------|-----------|----------------|
| Language | PHP | 8.x (supports 7.2+) |
| Database | MySQL / MariaDB | InnoDB engine |
| Web Server | Apache 2 | Via Docker |
| Frontend JS | MooTools | 1.x (legacy, ~2008) |
| CSS | Custom | T4 Travian-inspired |
| Infrastructure | Docker | docker-compose with 3 services |
| Version Control | Git | GitHub |
| Email | PHP mail() | SMTP support planned |

---

## Core Design Patterns

### 1. Monolithic "God Class" → Trait Refactoring (In Progress)

The codebase is undergoing a major refactoring:

- **Phase S1**: Split the massive `MYSQLi_DB` class (~5000+ lines) into 14 domain-specific traits
- **Phase S2**: Split the `Automation` class (~3000+ lines) into 13 domain-specific traits

**Before:**
```php
class MYSQLi_DB {
    // 5000+ lines of everything
}
```

**After:**
```php
class MYSQLi_DB implements IDbConnection {
    use DatabaseConnectionCore;
    use DatabaseUserQueries;
    use DatabaseVillageQueries;
    // ... 11 more traits
}
```

### 2. Hybrid Procedural/OOP Architecture

The project mixes:
- **Class-based OOP**: GameEngine classes (`Village`, `Building`, `Battle`, `Alliance`, etc.)
- **Global procedural**: `$database`, `$session`, `$generator` as global variables
- **Modern namespaced code**: `App\Entity\User`, `App\Utils\*` with autoloader
- **Template-inline PHP**: `.tpl` files with raw PHP embedded

### 3. Autoloader Architecture

A custom autoloader at project root handles the `App\` namespace:
```
App\Database\IDbConnection  → src/Database/IDbConnection.php
App\Entity\User             → src/Entity/User.php
App\Utils\AccessLogger      → src/Utils/AccessLogger.php
```

Legacy classes (global namespace) are included manually with pattern:
```php
for ($i = 0; $i < 5; $i++) {
    if (file_exists(str_repeat('../', $i) . 'autoloader.php')) {
        include_once str_repeat('../', $i) . 'autoloader.php';
        break;
    }
}
```

### 4. Cron-Based Game Loop

The entire game progresses via a cron system:
- `cron.php` is called periodically (every 60-300 seconds)
- `Automation.php` processes all pending actions in batch
- Lock file prevents concurrent automation runs
- Each cycle processes: resources, building queues, troop movements, battles, hero updates, medals

### 5. Database Caching Strategy

Per-request static caching inside the DB class:
```php
private static $fieldsCache = [];
private static $villageFieldsCache = [];
private static $unitCache = [];
// ... 40+ cache arrays
```

No external cache (Redis/Memcached). Cache cleared between requests.

---

## Key Subsystems

### Authentication & Authorization
- **Password Hashing**: `password_hash(PASSWORD_BCRYPT, ['cost' => 12])` - modern
- **Session**: Custom `Session` class with 30-second user cache
- **Email Verification**: Optional (`AUTH_EMAIL` config)
- **Sitter/Deputy System**: `sitterLogin()` for account sharing
- **Access Levels**: Admin (9), Multihunter (8), Player (2), Banned (0)

### Village System
- **Fields**: 18 resource fields (wood, clay, iron, crop) + 22 building slots
- **Build Queue**: `bdata` table, processed by AutomationBuildQueue
- **Demolition**: Optional building destruction
- **Production**: Hourly resource generation with building bonuses + oasis bonuses

### Military System
- **Unit Types**: Up to 11 unit types per tribe (u1-u11)
- **Tribes**: Romans (1), Teutons (2), Gauls (3), Nature (4), Natars (5), Huns (6), Egyptians (7), Spartans (8), Vikings (9)
- **Battle Resolution**: `Battle.php` - full simulation with casualties, wall bonuses, hero bonuses
- **Movement**: `movement` table tracks all troop movements with `sort_type` classification:
  - 0: Reinforcement
  - 1: Normal attack
  - 2: Raid
  - 3: Return
  - 4: Spy
  - 20/21: Hero adventure (out/back)
- **Prisoners**: Gauls' trapper building captures enemies

### Alliance System
- **Creation**: Requires Embassy level 3
- **Permissions**: 7-bit permission system (opt1-opt7)
- **Diplomacy**: Confederation, NAP, War
- **Forum**: Shared alliance forum with visibility controls
- **T4 Bonuses**: Recruitment, Philosophy, Metallurgy, Commerce

### Hero System (T4 port)
- **Level 1-120**: XP-based progression
- **Attributes**: Attack, Defense, Attack Bonus, Defense Bonus, Regeneration, Resources
- **Equipment**: Helmet, Armor, Boots, Left-hand, Right-hand, Horse (6 slots)
- **Adventures**: Travel to map tiles, fight monsters, get loot
- **Auction House**: Silver-based item trading between players
- **Death/Revival**: Hero can die, revival at level 37+ Tournament Square

### World Wonder System
- **Construction**: Building plan artifacts + massive resource requirements
- **Natars**: NPC tribe that spawns after a configurable time
- **Victory Condition**: First alliance to build WW to level 100 wins

### Anti-Cheat Systems
- **Push Protection**: Detects one-directional resource transfers; 7-day sliding window
- **Multi-Account Detection**: IP + User-Agent fingerprint correlation, login switching detection
- **Registration Blocklist**: Blocked usernames, emails, and domains
- **IP Bans**: varbinary(16) IPv4/IPv6 ban system

---

## Data Flow

```
HTTP Request → index.php/dorf1.php/build.php/etc.
    → Security.class.php (input sanitization)
    → Session validation
    → GameEngine class instantiation
        → Database queries (with per-request caching)
        → Business logic processing
    → Template rendering (.tpl files with PHP)
    → HTML response

Cron Request → cron.php
    → Automation.php (with lock file)
        → Process all pending queues
        → Resolve battles
        → Update resources
        → Award medals
        → Cleanup old data
```

---

## Configuration System

**Two-tier configuration:**
1. `GameEngine/config.php` - Generated during installation, contains ~100+ constants
2. Database `%PREFIX%config` table - Runtime mutable settings

Key constants include:
- `SPEED`, `INCREASE_SPEED`, `EVASION_SPEED` - Game speed multipliers
- `WORLD_MAX` - Map size (default 100x100 = 10,000 tiles)
- `STORAGE_MULTIPLIER` - Warehouse/granary capacity multiplier
- `TRADER_CAP`, `CRANNY_CAP`, `TRAPPER_CAP` - Building capacities
- `HERO_BASE_REGEN` - Hero HP regeneration per day
- `ALLIANCE_BONUSES` - T4 alliance bonus toggle
- `PLUS_STATS` - Travian Plus statistics toggle
- `CRON_LOOP`, `CRON_TICK` - Automation timing
- `GREAT_WKS`, `WW` - World Wonder settings

---

## Template System

The project uses a raw PHP template approach:
- `.tpl` files are directly `include()`d
- Templates have full access to all global variables
- No template inheritance, no escaping by default
- Templates are organized by feature in subdirectories
- Admin panel has its own separate template directory

Example template structure for building view:
```
Templates/Build/
    1.tpl        # Woodcutter
    2.tpl        # Clay Pit
    ...
    22_1.tpl     # Barracks (view 1)
    22_2.tpl     # Barracks (view 2)
    ...
    37_hero.tpl  # Hero's Mansion (hero view)
    37_items.tpl # Hero's Mansion (items view)
    37_adventures.tpl  # Hero's Mansion (adventures)
    ...
    ww.tpl       # World Wonder
    wwupgrade.tpl # WW upgrade
```

---

## Database Design Characteristics

- **~45 tables** with `s1_` prefix
- **InnoDB engine** throughout
- **Composite indexing** on frequently queried column pairs
- **No foreign keys** defined (relational integrity managed in application code)
- **Column naming**: Abbreviated (`vref`, `wref`, `uid`, `aid`, `gtype`, `gamt`)
- **Enumeration via integers**: Status fields use tinyint with code-level constants
- **Timestamp storage**: Unix timestamps as `int(11)` rather than MySQL TIMESTAMP/DATETIME
- **varbinary(16)** for IPv6-compatible IP storage (modern)

---

## Multilingual Support

Language files in `GameEngine/Lang/`:
- English (en), Chinese (zh), French (fr), Romanian (ro), Italian (it)
- Translation function: `rc_tok()` with token-based replacement
- Language selected at install time (`LANG` constant)
