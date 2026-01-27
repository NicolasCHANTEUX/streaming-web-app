# Script to apply database migrations

Write-Host "Applying database migrations..." -ForegroundColor Cyan

# Apply likes table migration
Write-Host "`nCreating liked_songs table..." -ForegroundColor Yellow

$mysql = Get-Command mysql -ErrorAction SilentlyContinue

if (-not $mysql) {
    Write-Host "Error: mysql command not found. Please ensure MySQL is in your PATH." -ForegroundColor Red
    exit 1
}

# Prompt for database credentials
$dbHost = Read-Host "Database host (default: localhost)"
if ([string]::IsNullOrWhiteSpace($dbHost)) { $dbHost = "localhost" }

$dbName = Read-Host "Database name (default: music_streaming)"
if ([string]::IsNullOrWhiteSpace($dbName)) { $dbName = "music_streaming" }

$dbUser = Read-Host "Database user (default: root)"
if ([string]::IsNullOrWhiteSpace($dbUser)) { $dbUser = "root" }

$dbPass = Read-Host "Database password" -AsSecureString
$dbPassPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPass))

# Apply migration
Get-Content "database_likes.sql" | mysql -h $dbHost -u $dbUser -p"$dbPassPlain" $dbName

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✓ Migration applied successfully!" -ForegroundColor Green
} else {
    Write-Host "`n✗ Migration failed!" -ForegroundColor Red
    exit 1
}

Write-Host "`nAll migrations completed!" -ForegroundColor Green
