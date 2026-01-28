# ============================================================================
# Music Streaming App - Script d'Initialisation Complet
# ============================================================================
# Ce script configure automatiquement l'application de A à Z
# Prérequis : PowerShell 5.1+, Droits administrateur recommandés
# ============================================================================

param(
    [switch]$SkipDependencies,
    [switch]$SkipDatabase,
    [string]$DbHost = "localhost",
    [string]$DbName = "music_streaming",
    [string]$DbUser = "root",
    [string]$DbPass = ""
)

$ErrorActionPreference = "Stop"
$ProgressPreference = "SilentlyContinue"

# Couleurs pour l'affichage
function Write-Header { param($Text) Write-Host "`n═══ $Text ═══`n" -ForegroundColor Cyan }
function Write-Success { param($Text) Write-Host "✓ $Text" -ForegroundColor Green }
function Write-Error { param($Text) Write-Host "✗ $Text" -ForegroundColor Red }
function Write-Info { param($Text) Write-Host "→ $Text" -ForegroundColor Yellow }
function Write-Step { param($Text) Write-Host "  • $Text" -ForegroundColor White }

Clear-Host
Write-Host @"
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║          🎵 MUSIC STREAMING APP - SETUP v3.0 🎵             ║
║                                                              ║
║  Installation complète avec toutes les fonctionnalités :     ║
║  • Player responsive avec système de likes                   ║
║  • File d'attente de téléchargements                         ║
║  • Modales personnalisées                                    ║
║  • Extraction de métadonnées et pochettes                    ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
"@ -ForegroundColor Cyan

# ============================================================================
# 1. VÉRIFICATION DES PRÉREQUIS
# ============================================================================

Write-Header "1. Vérification des prérequis"

$prereqsPassed = $true

# PHP
Write-Step "PHP 8.0+"
$php = Get-Command php -ErrorAction SilentlyContinue
if ($php) {
    $phpVersion = (php -v) -match "PHP (\d+\.\d+)" | Out-Null; $matches[1]
    if ([version]$phpVersion -ge [version]"8.0") {
        Write-Success "PHP $phpVersion détecté"
    } else {
        Write-Error "PHP $phpVersion trouvé, version 8.0+ requise"
        $prereqsPassed = $false
    }
} else {
    Write-Error "PHP non trouvé (requis)"
    $prereqsPassed = $false
}

# Composer
Write-Step "Composer"
$composer = Get-Command composer -ErrorAction SilentlyContinue
if ($composer) {
    Write-Success "Composer détecté"
} else {
    Write-Error "Composer non trouvé (requis)"
    $prereqsPassed = $false
}

# Node.js & npm
Write-Step "Node.js & npm"
$node = Get-Command node -ErrorAction SilentlyContinue
$npm = Get-Command npm -ErrorAction SilentlyContinue
if ($node -and $npm) {
    $nodeVersion = (node -v).Trim('v')
    Write-Success "Node.js $nodeVersion & npm détectés"
} else {
    Write-Error "Node.js/npm non trouvés (requis)"
    $prereqsPassed = $false
}

# MySQL
Write-Step "MySQL/MariaDB"
$mysql = Get-Command mysql -ErrorAction SilentlyContinue
if ($mysql) {
    Write-Success "MySQL client détecté"
} else {
    Write-Error "MySQL client non trouvé (requis)"
    $prereqsPassed = $false
}

# yt-dlp (optionnel, auto-détection)
Write-Step "yt-dlp (YouTube downloads)"
$ytdlpPath = "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\yt-dlp.yt-dlp_Microsoft.Winget.Source_8wekyb3d8bbwe\yt-dlp.exe"
if (Test-Path $ytdlpPath) {
    Write-Success "yt-dlp trouvé (WinGet)"
} else {
    $ytdlp = Get-Command yt-dlp -ErrorAction SilentlyContinue
    if ($ytdlp) {
        Write-Success "yt-dlp trouvé (PATH)"
    } else {
        Write-Info "yt-dlp non trouvé (optionnel, installation recommandée : winget install yt-dlp)"
    }
}

# ffmpeg (optionnel, auto-détection)
Write-Step "ffmpeg (conversion audio)"
$ffmpegPath = "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe\ffmpeg-8.0.1-full_build\bin\ffmpeg.exe"
if (Test-Path $ffmpegPath) {
    Write-Success "ffmpeg trouvé (WinGet)"
} else {
    $ffmpeg = Get-Command ffmpeg -ErrorAction SilentlyContinue
    if ($ffmpeg) {
        Write-Success "ffmpeg trouvé (PATH)"
    } else {
        Write-Info "ffmpeg non trouvé (optionnel, installation recommandée : winget install Gyan.FFmpeg)"
    }
}

if (-not $prereqsPassed) {
    Write-Error "`nPrérequis manquants. Veuillez les installer avant de continuer."
    exit 1
}

# ============================================================================
# 2. CRÉATION DE LA STRUCTURE DES DOSSIERS
# ============================================================================

Write-Header "2. Création de la structure des dossiers"

$directories = @(
    "storage/music",
    "storage/logs",
    "storage/cache",
    "public/assets/images/covers",
    "public/assets/css",
    "public/assets/js"
)

foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Step "Créé : $dir"
    } else {
        Write-Step "Existe : $dir"
    }
}

Write-Success "Structure des dossiers OK"

# ============================================================================
# 3. INSTALLATION DES DÉPENDANCES
# ============================================================================

if (-not $SkipDependencies) {
    Write-Header "3. Installation des dépendances"

    # Composer
    Write-Step "Installation des packages Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Packages Composer installés"
    } else {
        Write-Error "Échec de l'installation Composer"
        exit 1
    }

    # npm
    Write-Step "Installation des packages npm..."
    npm install --silent 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Packages npm installés"
    } else {
        Write-Error "Échec de l'installation npm"
        exit 1
    }

    # Compilation Tailwind CSS
    Write-Step "Compilation de Tailwind CSS..."
    npm run build 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Success "CSS compilé"
    } else {
        Write-Error "Échec de la compilation CSS"
        exit 1
    }
} else {
    Write-Info "Installation des dépendances ignorée (--SkipDependencies)"
}

# ============================================================================
# 4. CONFIGURATION DE LA BASE DE DONNÉES
# ============================================================================

if (-not $SkipDatabase) {
    Write-Header "4. Configuration de la base de données"

    # Demander le mot de passe si non fourni
    if ([string]::IsNullOrWhiteSpace($DbPass)) {
        $securePass = Read-Host "Mot de passe MySQL pour '$DbUser' (vide si aucun)" -AsSecureString
        $DbPass = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
            [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePass)
        )
    }

    Write-Step "Connexion à MySQL..."
    
    # Test de connexion
    $testCmd = "SELECT 1;"
    if ([string]::IsNullOrWhiteSpace($DbPass)) {
        echo $testCmd | mysql -h $DbHost -u $DbUser 2>&1 | Out-Null
    } else {
        echo $testCmd | mysql -h $DbHost -u $DbUser -p"$DbPass" 2>&1 | Out-Null
    }
    
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Impossible de se connecter à MySQL"
        exit 1
    }
    Write-Success "Connexion MySQL OK"

    # Création de la base de données
    Write-Step "Création de la base de données '$DbName'..."
    $createDbCmd = "CREATE DATABASE IF NOT EXISTS ``$DbName`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    if ([string]::IsNullOrWhiteSpace($DbPass)) {
        echo $createDbCmd | mysql -h $DbHost -u $DbUser 2>&1 | Out-Null
    } else {
        echo $createDbCmd | mysql -h $DbHost -u $DbUser -p"$DbPass" 2>&1 | Out-Null
    }
    Write-Success "Base de données '$DbName' prête"

    # Application du schéma principal
    Write-Step "Création des tables (songs, playlists, playlist_songs)..."
    if (Test-Path "database.sql") {
        if ([string]::IsNullOrWhiteSpace($DbPass)) {
            Get-Content "database.sql" | mysql -h $DbHost -u $DbUser $DbName 2>&1 | Out-Null
        } else {
            Get-Content "database.sql" | mysql -h $DbHost -u $DbUser -p"$DbPass" $DbName 2>&1 | Out-Null
        }
        
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Schéma principal appliqué"
        } else {
            Write-Error "Échec de l'application du schéma"
            exit 1
        }
    }

    # Application de la migration des likes
    Write-Step "Création de la table liked_songs..."
    if (Test-Path "database_likes.sql") {
        if ([string]::IsNullOrWhiteSpace($DbPass)) {
            Get-Content "database_likes.sql" | mysql -h $DbHost -u $DbUser $DbName 2>&1 | Out-Null
        } else {
            Get-Content "database_likes.sql" | mysql -h $DbHost -u $DbUser -p"$DbPass" $DbName 2>&1 | Out-Null
        }
        
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Table liked_songs créée"
        } else {
            Write-Info "Table liked_songs déjà existante ou erreur mineure"
        }
    }

    # Création du fichier .env
    Write-Step "Création du fichier .env..."
    $envContent = @"
DB_HOST=$DbHost
DB_NAME=$DbName
DB_USER=$DbUser
DB_PASS=$DbPass

APP_ENV=development
APP_DEBUG=true
"@
    $envContent | Out-File -FilePath ".env" -Encoding UTF8
    Write-Success "Fichier .env créé"

} else {
    Write-Info "Configuration de la base de données ignorée (--SkipDatabase)"
}

# ============================================================================
# 5. CONFIGURATION DES PERMISSIONS (Windows)
# ============================================================================

Write-Header "5. Configuration des permissions"

$writableDirs = @("storage", "public/assets/images/covers")

foreach ($dir in $writableDirs) {
    if (Test-Path $dir) {
        # Sur Windows, on s'assure juste que les dossiers ne sont pas en lecture seule
        Get-ChildItem -Path $dir -Recurse | ForEach-Object {
            if ($_.Attributes -band [System.IO.FileAttributes]::ReadOnly) {
                $_.Attributes = $_.Attributes -bxor [System.IO.FileAttributes]::ReadOnly
            }
        }
        Write-Step "Permissions OK : $dir"
    }
}

Write-Success "Permissions configurées"

# ============================================================================
# 6. VÉRIFICATION FINALE
# ============================================================================

Write-Header "6. Vérification finale"

$checks = @(
    @{ Name = "Autoloader Composer"; Path = "vendor/autoload.php" },
    @{ Name = "CSS compilé"; Path = "public/assets/css/output.css" },
    @{ Name = "Config PHP"; Path = "config.php" },
    @{ Name = "Index public"; Path = "public/index.php" },
    @{ Name = "Dossier storage/music"; Path = "storage/music" },
    @{ Name = "Dossier covers"; Path = "public/assets/images/covers" }
)

$allChecksPass = $true
foreach ($check in $checks) {
    if (Test-Path $check.Path) {
        Write-Step "$($check.Name) : OK"
    } else {
        Write-Error "$($check.Name) : MANQUANT"
        $allChecksPass = $false
    }
}

if ($allChecksPass) {
    Write-Success "Tous les fichiers critiques sont présents"
} else {
    Write-Error "Certains fichiers sont manquants"
    exit 1
}

# ============================================================================
# 7. RÉCAPITULATIF ET INSTRUCTIONS
# ============================================================================

Write-Host "`n"
Write-Host "╔══════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║                                                              ║" -ForegroundColor Green
Write-Host "║            ✓ INSTALLATION TERMINÉE AVEC SUCCÈS !            ║" -ForegroundColor Green
Write-Host "║                                                              ║" -ForegroundColor Green
Write-Host "╚══════════════════════════════════════════════════════════════╝" -ForegroundColor Green

Write-Host "`n📋 RÉCAPITULATIF :" -ForegroundColor Cyan
Write-Host "   • Base de données : $DbName@$DbHost" -ForegroundColor White
Write-Host "   • Tables créées : songs, playlists, playlist_songs, liked_songs" -ForegroundColor White
Write-Host "   • Dépendances : Composer + npm installées" -ForegroundColor White
Write-Host "   • CSS : Tailwind compilé" -ForegroundColor White
Write-Host "   • Dossiers : storage/, public/assets/images/covers/" -ForegroundColor White

Write-Host "`n🚀 DÉMARRAGE :" -ForegroundColor Cyan
Write-Host "   1. Démarrer le serveur PHP :" -ForegroundColor White
Write-Host "      cd public" -ForegroundColor Yellow
Write-Host "      php -S localhost:8080" -ForegroundColor Yellow
Write-Host ""
Write-Host "   2. Ouvrir dans le navigateur :" -ForegroundColor White
Write-Host "      http://localhost:8080" -ForegroundColor Yellow

Write-Host "`n🎵 FONCTIONNALITÉS DISPONIBLES :" -ForegroundColor Cyan
Write-Host "   ✓ Player audio responsive avec contrôles mobiles" -ForegroundColor White
Write-Host "   ✓ Système de likes (page /liked)" -ForegroundColor White
Write-Host "   ✓ File d'attente de téléchargements YouTube" -ForegroundColor White
Write-Host "   ✓ Modales personnalisées pour les playlists" -ForegroundColor White
Write-Host "   ✓ Extraction automatique de métadonnées et pochettes" -ForegroundColor White
Write-Host "   ✓ Mode sombre par défaut" -ForegroundColor White

Write-Host "`n📚 COMMANDES UTILES :" -ForegroundColor Cyan
Write-Host "   • Recompiler le CSS :" -ForegroundColor White
Write-Host "     npm run build" -ForegroundColor Yellow
Write-Host ""
Write-Host "   • Appliquer de nouvelles migrations :" -ForegroundColor White
Write-Host "     .\migrate.ps1" -ForegroundColor Yellow
Write-Host ""
Write-Host "   • Réinstaller les dépendances :" -ForegroundColor White
Write-Host "     composer install && npm install" -ForegroundColor Yellow

Write-Host "`n💡 ASTUCE :" -ForegroundColor Cyan
Write-Host "   Pour installer yt-dlp et ffmpeg (téléchargements YouTube) :" -ForegroundColor White
Write-Host "   winget install yt-dlp" -ForegroundColor Yellow
Write-Host "   winget install Gyan.FFmpeg" -ForegroundColor Yellow

Write-Host "`n🎉 Bon streaming ! 🎵`n" -ForegroundColor Magenta
