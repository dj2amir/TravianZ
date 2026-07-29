<?php
###############################  S  T  A  R  T   ################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       config.php                                                  ##
##  Version        10.0 Full Refactor & Security                               ##
##  Developed by:  Dzoki and Dixie Edited by Advocaite                         ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2013-2026. All rights reserved.                ##
##  Modified by:   Shadow and ronix                                            ##
##                                                                             ##
##  URLs:          https://travianz.org                                        ##
##                 https://github.com/Shadowss/TravianZ                        ##
#################################################################################

//////////////////////////////////
// *****  ERROR REPORTING  *****//
//////////////////////////////////
// (E_ALL ^ E_NOTICE) = enabled
// (0) = disabled
define("ERROR_REPORT","E_ALL ^ E_NOTICE ^ E_DEPRECATED");
error_reporting (E_ALL ^ E_NOTICE ^ E_DEPRECATED);
define('AUTOMATION_LOCK_FILE_NAME', 'automation.lck');

//////////////////////////////////
// *****  CRON / AUTOMATION *****//
//////////////////////////////////
// Automation runs from cron.php (a server cron job), not from player page
// requests. See the comments in cron.php for cron job installation instructions.
//
// CRON_LOOP_SECONDS = how long a single cron.php invocation runs.
//   Many hosting providers do not allow cron jobs to run more frequently than
//   every 5 minutes, while Automation is designed to run approximately every
//   60 seconds. For this reason, a single invocation executes multiple ticks
//   in sequence.
//   300 = suitable for a "*/5 * * * *" cron schedule.
//   Set to 0 if your hosting provider allows a cron job every minute
//   (in that case, each invocation executes only a single tick).
//
// CRON_TICK_SECONDS = the interval, in seconds, between each tick within a
// single cron.php invocation.
define('CRON_LOOP_SECONDS', 0);
define('CRON_TICK_SECONDS', 60);

// Key used to access cron.php via HTTP (wget/curl or an external cron service).
// Command-line execution (e.g. a cPanel cron job) does NOT require it.
// Automatically generated during installation and preserved when saving configuration settings from the ACP.
define('CRON_KEY', 'testcron123');

//////////////////////////////////
// *****  DATABASE CLEANUP  *****//
//////////////////////////////////
//
// Tables that grow indefinitely (reports, chat, deleted messages) are cleaned
// up periodically by Automation. Set each rule to 0 to disable it individually.
//
// Reports archived by players are never deleted.
define('CLEANUP_REPORTS_DAYS', 0);
define('CLEANUP_CHAT_DAYS', 0);
define('CLEANUP_MESSAGES_DAYS', 0);
define('CLEANUP_INTERVAL', 3600);
define('CLEANUP_BATCH', 5000);

//////////////////////////////////
// *****       HERO        *****//
//////////////////////////////////
//
// The hero's BASE health regeneration, in HP per day, independent of the
// points invested in the Regeneration attribute (as in Travian T4).
// Without this, a hero with 0 points in Regeneration would never recover
// health and would eventually die after enough adventures.
//
// It scales with the server speed, just like regeneration from attributes.
// Set to 0 to disable it (legacy behavior).
define('HERO_BASE_REGEN', 5);

// Auction House exchange rates:
//
//   HERO_SILVER_PER_GOLD = how much silver you receive for 1 gold
//   HERO_SILVER_TO_GOLD  = how much silver it costs to buy 1 gold
//
// The difference between the two rates is the Auction House margin
// (just like in Travian: 1 gold → 10 silver, but 25 silver → 1 gold).
define('HERO_SILVER_PER_GOLD', 10);
define('HERO_SILVER_TO_GOLD', 25);

// Hero "Resources" attribute (T4): how many resources each attribute point
// produces per hour.
//
//   ALL = when the bonus is distributed equally across all four resources
//         (default: 3 of each resource)
//
//   ONE = when the bonus is concentrated on a single resource
//         (default: 10)
define('HERO_RES_PER_POINT_ALL', 3);
define('HERO_RES_PER_POINT_ONE', 10);

//////////////////////////////////
// *****  SERVER SETTINGS  *****//
//////////////////////////////////

// ***** Name
define("SERVER_NAME","ZarinpalTest");

// ***** Time zone added by ronix
// Defines server time zone.
define("TIMEZONE","Asia/Tehran");
date_default_timezone_set(TIMEZONE);

// ***** Started
// Defines when has server started.
define("COMMENCE","1785221727");

// ***** Server Start Date / Time
define("START_DATE", "29/07/2026");
define("START_TIME", "06:55");

// ***** Language
// SERVER_LANG is the DEFAULT language of the server (chosen at install / in
// the admin "Server Settings"). LANG is the EFFECTIVE display language.
//
// Per-user language (issue #166): if the logged-in player picked a language
// in their profile preferences (stored in users.lang and mirrored into
// $_SESSION['lang']), LANG becomes that language; otherwise LANG falls back
// to SERVER_LANG.
//
// SECURITY: LANG is used in include("Lang/".LANG.".php"), so the value is
// strictly sanitized to [a-z_] (no path traversal) and the target file MUST
// exist, otherwise we fall back to the server default. This prevents Local
// File Inclusion via a crafted session value.
define("SERVER_LANG", "fa");
if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
$__user_lang = isset($_SESSION['lang']) ? preg_replace('/[^a-z_]/', '', strtolower((string) $_SESSION['lang'])) : '';
define("LANG", ($__user_lang !== '' && is_file(__DIR__ . "/Lang/" . $__user_lang . ".php")) ? $__user_lang : SERVER_LANG);

// ***** Speed
// Choose your server speed. NOTICE: Higher speed, more likely
// to have some bugs. Lower speed, most likely no major bugs.
// Values: 1 (normal), 3 (3x speed) etc...
define("SPEED", "1");

// ***** World size
// Defines world size. NOTICE: DO NOT EDIT!!
define("WORLD_MAX", "25");

// ***** Alliance Bonuses (T4 Port)
// Members donate resources, allowing the alliance to unlock four bonuses, each
// with five levels. The costs, durations, and limits below are based on
// Travian T4; upgrade times and donation limits are scaled by the server speed.
define("NEW_FUNCTIONS_ALLIANCE_BONUSES", true);

// Total resources required for each level (cumulative for that level).
define("ALLIANCE_BONUS_COSTS", "1200000,5600000,17100000,51200000,153600000");

// Upgrade duration in HOURS for each level (divided by the server speed).
define("ALLIANCE_BONUS_HOURS", "24,48,72,96,120");

// Daily donation limit per player, based on the highest bonus level unlocked
// by the alliance (index 0 = no bonuses unlocked).
define("ALLIANCE_BONUS_DAILY", "300000,300000,400000,550000,750000,1000000");

// Percentage granted by each level. Two sets: "small" bonuses (2% per level:
// Recruitment, Philosophy) and "large" bonuses (4% per level: Metallurgy,
// Commerce), exactly as in T4.
define("ALLIANCE_BONUS_PCT_SMALL", 2);
define("ALLIANCE_BONUS_PCT_LARGE", 4);

// Gold cost to triple a donation.
define("ALLIANCE_BONUS_TRIPLE_GOLD", 3);

// ***** Graphical statistics (Travian Plus)
// The game periodically records each player's rank, population, villages, and
// army from the moment this feature is enabled. These snapshots are then used
// to generate the account progression graphs.
//
// Data is collected for ALL players, but the Statistics tab is visible ONLY to
// users with an active Plus account. Otherwise, players who purchase Plus would
// open the page and see an empty graph immediately after paying.
define("NEW_FUNCTIONS_PLUS_STATISTICS", true);

// Number of hours between snapshots. On a fast server, a single day represents
// a significant amount of gameplay, so taking a snapshot every 6 hours provides
// a smooth graph without filling the database table too quickly.
define("PLUS_STATS_INTERVAL_HOURS", 6);

// Number of days to retain historical data. Set to 0 to keep all snapshots,
// allowing the complete account progression to be displayed. Even over the
// lifetime of an entire server, this only amounts to a few tens of thousands of records.
define("PLUS_STATS_KEEP_DAYS", 0);

// ***** Graphic Pack
//
// SERVER_GP is the pack every player sees by default (chosen at install or in
// Admin -> Server Configuration). GP_ENABLE decides whether players may pick a
// different pack for themselves in Profile -> Graphic Pack.
//
// GP_LOCATE is the pack ACTUALLY used for the current request. It follows the
// same pattern as LANG above: the player's own choice wins when it is enabled
// and points at a real pack on disk, otherwise the server pack is used. A pack
// counts as real when the folder exists and contains travian.css, so a stale
// value in the database can never leave the game without stylesheets.
define("GP_ENABLE",true);
define("SERVER_GP", "gpack/travian/");

$__user_gp = '';

if (GP_ENABLE && isset($_SESSION['gpack']) && is_string($_SESSION['gpack'])) {
    $__candidate = trim((string) $_SESSION['gpack']);

    // only local packs: "gpack/<name>/" with no traversal and no remote URL
    if (preg_match('#^gpack/[A-Za-z0-9_\-]+/$#', $__candidate)
        && is_file(__DIR__ . "/../" . $__candidate . "travian.css")) {
        $__user_gp = $__candidate;
    }
}

define("GP_LOCATE", $__user_gp !== '' ? $__user_gp : SERVER_GP);

// ***** Troop Speed
// Values: 1 (normal), 3 (3x speed) etc...
define("INCREASE_SPEED","1");

// ***** Evasion Speed
define("EVASION_SPEED","1");

// ***** Trader capacity
// Values: 1 (normal), 3 (3x speed) etc...
define("TRADER_CAPACITY","1");

// ***** Cranny capacity
define("CRANNY_CAPACITY","1");

// ***** Trapper capacity
define("TRAPPER_CAPACITY","1");

// ***** Village Expand
// 1 = slow village expanding - more Cultural Points needed for every new village
// 0 = fast village expanding - less Cultural Points needed for every new village
define("CP", 1);

// ***** Demolish Level Required
// Defines which level of Main building is required to be able to
// demolish. Min value = 1, max value = 20
// Default: 10
define("DEMOLISH_LEVEL_REQ","10");

// ***** Change storage capacity
define("STORAGE_MULTIPLIER","1");
define("STORAGE_BASE",800*STORAGE_MULTIPLIER);

// ***** Quest
// Ingame quest enabled/disabled.
define("QUEST",true);
//quest type : 25 = Travian Official 
//             37 = TravianZ Extended 
define("QTYPE",37);

// ***** Beginners Protection
// 3600 = 1 hour
// 3600*12 = 12 hours
// 3600*24 = 1 day
// 3600*24*3 = 3 days
// You can choose any value you want!
define("PROTECTION","3600");

// ***** Enable WW Statistics
define("WW",false);

// ***** Show Natars in Statistics
define("SHOW_NATARS",false); 

// ***** Natars Units Multiplier
define("NATARS_UNITS",1); 

// ***** Natars Spawn Time
define("NATARS_SPAWN_TIME",2592000); 
define("NATARS_WW_SPAWN_TIME",5184000); 
define("NATARS_WW_BUILDING_PLAN_SPAWN_TIME",8640000); 

// ***** Nature troops regeneration time
define("NATURE_REGTIME",86400); 

// ***** Oasis production
define("OASIS_WOOD_MULTIPLIER",1); 
define("OASIS_CLAY_MULTIPLIER",1); 
define("OASIS_IRON_MULTIPLIER",1); 
define("OASIS_CROP_MULTIPLIER",1); 
define("OASIS_WOOD_PRODUCTION",OASIS_WOOD_MULTIPLIER*SPEED);
define("OASIS_CLAY_PRODUCTION",OASIS_CLAY_MULTIPLIER*SPEED);
define("OASIS_IRON_PRODUCTION",OASIS_IRON_MULTIPLIER*SPEED);
define("OASIS_CROP_PRODUCTION",OASIS_CROP_MULTIPLIER*SPEED); 

// ***** Enable T4 is Coming screen
define("T4_COMING",false);

// ***** Activation Mail
// true = activation mail will be sent, users will have to finish registration
//        by clicking on link recieved in mail.
// false =  users can register with any mail. Not needed to be real one.
define("AUTH_EMAIL",false);

// ***** PLUS
//Plus PayPal e-mail address
define("PAYPAL_EMAIL","");
//Plus PayPal currency
define("PAYPAL_CURRENCY","EUR");
//Plus Package A Price
define("PLUS_PACKAGE_A_PRICE","1,99");
//Plus Package A Gold
define("PLUS_PACKAGE_A_GOLD","60");
//Plus Package B Price
define("PLUS_PACKAGE_B_PRICE","4,99");
//Plus Package B Gold
define("PLUS_PACKAGE_B_GOLD","120");
//Plus Package C Price
define("PLUS_PACKAGE_C_PRICE","9,99");
//Plus Package C Gold
define("PLUS_PACKAGE_C_GOLD","360");
//Plus Package D Gold
define("PLUS_PACKAGE_D_GOLD","1000");
//Plus Package D Price
define("PLUS_PACKAGE_D_PRICE","19,99");
//Plus Package E Price
define("PLUS_PACKAGE_E_PRICE","49,99");
//Plus Package E Gold
define("ZARINPAL_MERCHANT_ID","aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee");
define("ZARINPAL_SANDBOX",true);
define("ZP_PACKAGE_A_RIAL","199000");
define("ZP_PACKAGE_B_RIAL","499000");
define("ZP_PACKAGE_C_RIAL","999000");
define("ZP_PACKAGE_D_RIAL","1999000");
define("ZP_PACKAGE_E_RIAL","4999000");

define("PLUS_PACKAGE_E_GOLD","2000");
//Plus account lenght
define("PLUS_TIME",86400);
//+25% production lenght
define("PLUS_PRODUCTION",86400);
// ***** Medal Interval check
define("MEDALINTERVAL",1);
// ***** Great Workshop
define("GREAT_WKS",false);
// ***** Tourn threshold
define("TS_THRESHOLD",5);  

// ***** Register open/close
define("REG_OPEN",true);

// ***** Peace system
// 0 = None
// 1 = Normal
// 2 = Christmas
// 3 = New Year
// 4 = Easter
define("PEACE",0);

//////////////////////////////////
//    **** LOG SETTINGS  ****   //
//////////////////////////////////
// LOG BUILDING/UPGRADING
define("LOG_BUILD",true);
// LOG RESEARCHES
define("LOG_TECH",true);
// LOG USER LOGIN (IP's)
define("LOG_LOGIN",true);
// LOG GOLD
define("LOG_GOLD_FIN",true);
// LOG ADMIN
define("LOG_ADMIN",true);
// LOG ATTACK REPORTS
define("LOG_WAR",true);
// LOG MARKET REPORTS
define("LOG_MARKET",true);
// LOG ILLEGAL ACTIONS
define("LOG_ILLEGAL",true);



//////////////////////////////////
// ****  NEWSBOX SETTINGS  **** //
//////////////////////////////////
//true = enabled
//false = disabled
define("NEWSBOX1",false);
define("NEWSBOX2",false);
define("NEWSBOX3",false);



//////////////////////////////////
//   ****  SQL SETTINGS  ****   //
//////////////////////////////////

// ***** SQL Hostname
// example: sql106.000space.com / localhost
// If you host server on own PC than this value is: localhost
// If you use online hosting, value must be written in host cpanel
define("SQL_SERVER", "localhost");

// ***** SQL Port
// default: 3306
define("SQL_PORT", 3306);

// ***** Database Username
define("SQL_USER", "root");

// ***** Database Password
define("SQL_PASS", "");

// ***** Database Name
define("SQL_DB", "travianz_zp_test");

// ***** Database - Table Prefix
define("TB_PREFIX", "s1_");

// ***** Database type
// 0 = MYSQL
// 1 = MYSQLi
// default: 1
define("DB_TYPE", 1);



////////////////////////////////////
//   ****  EXTRA SETTINGS  ****   //
////////////////////////////////////

// ***** Censore words
//define("WORD_CENSOR", "%ACTCEN%");

// ***** Words (censore)
// Choose which words do you want to be censored
//define("CENSORED","%CENWORDS%");


// ***** Limit Mailbox
// Limits mailbox to defined number of mails. (IGM's)
define("LIMIT_MAILBOX",false);
// If enabled, define number of maximum mails.
define("MAX_MAIL","30");

// ***** Include administrator in statistics/rank
define("INCLUDE_ADMIN", false);



////////////////////////////////////
//   ****  ADMIN SETTINGS  ****   //
////////////////////////////////////

// ***** Admin Email
define("ADMIN_EMAIL", "admin@test.local");

// ***** Admin Name
define("ADMIN_NAME", "testadmin");

// ***** Show Support Messages in Admin
define("ADMIN_RECEIVE_SUPPORT_MESSAGES", true);

// ***** Allow Admin accounts to be raided and attacked
define("ADMIN_ALLOW_INCOMING_RAIDS", true);


/////////////////////////////////////////////////
//   ****  NEW MECHANICS AND FUNCTIONS  ****   //
/////////////////////////////////////////////////
define("NEW_FUNCTIONS_OASIS", true);
define("NEW_FUNCTIONS_ALLIANCE_INVITATION", true);
define("NEW_FUNCTIONS_EMBASSY_MECHANICS", true);
define("NEW_FUNCTIONS_FORUM_POST_MESSAGE", true);
define("NEW_FUNCTIONS_TRIBE_IMAGES", true);
define("NEW_FUNCTIONS_MHS_IMAGES", true);
define("NEW_FUNCTIONS_DISPLAY_ARTIFACT", true);
define("NEW_FUNCTIONS_DISPLAY_WONDER", true);
define("NEW_FUNCTIONS_VACATION", true);
define("NEW_FUNCTIONS_DISPLAY_CATAPULT_TARGET", true);
define("NEW_FUNCTIONS_MANUAL_NATURENATARS", true);
define("NEW_FUNCTIONS_DISPLAY_LINKS", true);
define("NEW_FUNCTIONS_MEDAL_3YEAR", false);
define("NEW_FUNCTIONS_MEDAL_5YEAR", false);
define("NEW_FUNCTIONS_MEDAL_10YEAR", false);
define("NEW_FUNCTIONS_SPECIAL_MEDALS_SYSTEM", true);
define("NEW_FUNCTIONS_MILESTONES", true);
define("NEW_FUNCTIONS_MEDAL_RESET", false);
define("NEW_FUNCTIONS_HERO_T4", true);
define("NEW_FUNCTION_TRIBE_HUNS", true);
define("NEW_FUNCTION_TRIBE_EGIPTEANS", true);
define("NEW_FUNCTION_TRIBE_SPARTANS", true);
define("NEW_FUNCTION_TRIBE_VIKINGS", true);
define("NEW_FUNCTION_REGISTRATION_GOLD", false);
define("NEW_FUNCTION_REGISTRATION_GOLD_VALUE", 200);

//////////////////////////////////////////
//   ****  DO NOT EDIT SETTINGS  ****   //
//////////////////////////////////////////
define("AUTO_DEL_INACTIVE",false); // auto-delete inactive players; default = false
define("UN_ACT_TIME", 3628800); // 6 weeks to consider a player inactive
//define("TRACK_USR","%UTRACK%");
//define("USER_TIMEOUT","%UTOUT%");
define("TRACK_USR",true); // track users' being active or not
define("USER_TIMEOUT",3600); // 1 hour of no activity counts as inactivity
define("ALLOW_BURST",false);
define("BASIC_MAX",1);
define("INNER_MAX",1);
define("PLUS_MAX",1);
define("ALLOW_ALL_TRIBE",false);
define("CFM_ADMIN_ACT",true);
define("SERVER_WEB_ROOT",false);
define("USRNM_SPECIAL",true);
define("USRNM_MIN_LENGTH",3);
define("USRNM_MAX_LENGTH",15);
define("PW_MIN_LENGTH",4);

// === IP ban (issue #185) ===
// Master switch for IP-ban enforcement.
define("BAN_IP_ENABLED",true);
// Comma-separated list of trusted proxy IPs/CIDRs allowed to set the forwarded
// header. Leave EMPTY for direct access (REMOTE_ADDR only - non-spoofable).
// Reverse proxy example: "127.0.0.1,::1"  |  set to your proxy/Cloudflare ranges.
define("IP_TRUSTED_PROXIES","");
// $_SERVER key read for the real client IP when behind a trusted proxy.
// Cloudflare: use "HTTP_CF_CONNECTING_IP".
define("IP_FORWARDED_HEADER","HTTP_X_FORWARDED_FOR");
define("BANNED",0);
define("AUTH",1);
define("USER",2);
define("MULTIHUNTER",8);
define("ADMIN",9);
define("COOKIE_EXPIRE", 60*60*24*7); 
define("COOKIE_PATH", "/"); 
define("LOG_PAGE_ACCESS", false);
define("PAGE_ACCESS_LOG_DATE", true);
define("PAGE_ACCESS_LOG_IP", true);
define("PAGE_ACCESS_LOG_FILENAME", 'access.log'); // filename ONLY, no path!


////////////////////////////////////////////
//   ****  DOMAIN/SERVER SETTINGS  ****   //
////////////////////////////////////////////
define("DOMAIN", "localhost");
define("HOMEPAGE", "http://localhost:8080/");
define("SERVER", "http://localhost:8080/");

$requse = 0;

###############################  E    N    D   ##################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       config.php                                                  ##
##  Version        10.0 Full Refactor & Security                               ##
##  Developed by:  Dzoki and Dixie Edited by Advocaite                         ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2026. All rights reserved.                ##
##                                                                             ##
#################################################################################

?>
