/**
 * Import Spotify CSV - Mass Download Manager
 * Handles CSV parsing and sequential download queue
 */

console.log('=== IMPORT.JS LOADED - VERSION 2.0 ===');

let csvData = [];
let importQueue = [];
let currentIndex = 0;
let isImporting = false;
let isCancelled = false;

// Statistics
let stats = {
    downloaded: 0,
    already_exists: 0,
    skipped: 0,
    errors: 0,
    no_results: 0
};

/**
 * Handle file selection
 */
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Display filename
    document.getElementById('fileName').textContent = `📄 ${file.name}`;
    document.getElementById('fileName').classList.remove('hidden');

    // Read and parse CSV
    const reader = new FileReader();
    reader.onload = function(e) {
        const text = e.target.result;
        parseCSV(text);
    };
    reader.readAsText(file, 'UTF-8');
}

/**
 * Parse CSV file
 * Expected format: "Track URI","Track Name","Artist Name(s)","Album Name",...
 */
function parseCSV(csvText) {
    console.log('=== Starting CSV Parse ===');
    const lines = csvText.split('\n');
    
    if (lines.length < 2) {
        log('❌ Fichier CSV vide ou invalide', 'text-red-500');
        return;
    }

    // Parse header to find column indices
    const header = lines[0];
    console.log('Header:', header);
    
    // Find column indices (case insensitive)
    const headerLower = header.toLowerCase();
    let trackNameIndex = -1;
    let artistNameIndex = -1;
    let trackUriIndex = -1;

    // Split header intelligently (respecting quotes)
    const headerCols = parseCSVLine(header);
    
    headerCols.forEach((col, index) => {
        const colLower = col.toLowerCase().trim();
        if (colLower.includes('track name') || colLower === 'title') {
            trackNameIndex = index;
        } else if (colLower.includes('artist name')) {
            artistNameIndex = index;
        } else if (colLower.includes('track uri') || colLower === 'uri') {
            trackUriIndex = index;
        }
    });

    console.log('Column indices:', { trackNameIndex, artistNameIndex, trackUriIndex });

    if (trackNameIndex === -1 || artistNameIndex === -1) {
        log('❌ Colonnes requises non trouvées (Track Name, Artist Name)', 'text-red-500');
        alert('Format CSV invalide. Assurez-vous que le fichier contient les colonnes "Track Name" et "Artist Name(s)".');
        return;
    }

    // Parse data rows
    csvData = [];
    let invalidCount = 0;

    for (let i = 1; i < lines.length; i++) {
        const line = lines[i].trim();
        if (!line) continue;

        const columns = parseCSVLine(line);
        
        const trackName = columns[trackNameIndex]?.trim();
        const artistName = columns[artistNameIndex]?.trim();
        const trackUri = trackUriIndex >= 0 ? columns[trackUriIndex]?.trim() : '';

        if (trackName && artistName) {
            csvData.push({
                title: trackName,
                artist: artistName,
                uri: trackUri,
                lineNumber: i + 1
            });
        } else {
            invalidCount++;
            console.warn(`Line ${i + 1}: Missing data - Title: "${trackName}", Artist: "${artistName}"`);
        }
    }

    console.log(`Parsed ${csvData.length} valid tracks, ${invalidCount} invalid lines`);

    // Update UI
    document.getElementById('totalTracks').textContent = csvData.length + invalidCount;
    document.getElementById('readyTracks').textContent = csvData.length;
    document.getElementById('invalidTracks').textContent = invalidCount;
    
    document.getElementById('statsPreview').classList.remove('hidden');
    document.getElementById('controlButtons').classList.remove('hidden');

    if (csvData.length > 0) {
        log(`✓ ${csvData.length} titres prêts à importer`, 'text-green-500');
    } else {
        log('❌ Aucun titre valide trouvé dans le fichier', 'text-red-500');
    }
}

/**
 * Parse a single CSV line (handles quoted values with commas)
 */
function parseCSVLine(line) {
    const result = [];
    let current = '';
    let inQuotes = false;

    for (let i = 0; i < line.length; i++) {
        const char = line[i];
        const nextChar = line[i + 1];

        if (char === '"') {
            if (inQuotes && nextChar === '"') {
                // Escaped quote
                current += '"';
                i++; // Skip next quote
            } else {
                // Toggle quote mode
                inQuotes = !inQuotes;
            }
        } else if (char === ',' && !inQuotes) {
            // End of field
            result.push(current);
            current = '';
        } else {
            current += char;
        }
    }

    // Add last field
    result.push(current);

    return result.map(field => field.trim());
}

/**
 * Start import process
 */
async function startImport() {
    if (csvData.length === 0) {
        alert('Aucun titre à importer');
        return;
    }

    if (isImporting) {
        alert('Une importation est déjà en cours');
        return;
    }

    isImporting = true;
    isCancelled = false;
    currentIndex = 0;
    importQueue = [...csvData]; // Clone array

    // Reset statistics
    stats = {
        downloaded: 0,
        already_exists: 0,
        skipped: 0,
        errors: 0,
        no_results: 0
    };

    // Update UI
    document.getElementById('startImportBtn').classList.add('hidden');
    document.getElementById('cancelImportBtn').classList.remove('hidden');
    document.getElementById('progressSection').classList.remove('hidden');
    document.getElementById('statsSection').classList.remove('hidden');
    document.getElementById('importLogsSection').classList.remove('hidden');
    
    document.getElementById('totalProgress').textContent = importQueue.length;
    document.getElementById('currentProgress').textContent = 0;

    log('🚀 Début de l\'importation...', 'text-blue-500');
    console.log('DEBUG: importQueue.length =', importQueue.length);
    console.log('DEBUG: First 3 tracks:', importQueue.slice(0, 3));

    // Process queue
    try {
        await processImportQueue();
        console.log('DEBUG: processImportQueue completed');
    } catch (error) {
        console.error('ERROR in processImportQueue:', error);
        log(`❌ Erreur fatale: ${error.message}`, 'text-red-500');
    }

    // Finished
    isImporting = false;
    document.getElementById('cancelImportBtn').classList.add('hidden');
    document.getElementById('startImportBtn').classList.remove('hidden');
    document.getElementById('startImportBtn').innerHTML = '<i class="fas fa-redo"></i> Recommencer';
    
    if (isCancelled) {
        log('⚠️ Importation annulée par l\'utilisateur', 'text-yellow-500');
    } else {
        log('✅ Importation terminée!', 'text-green-500');
        log(`📊 Résumé: ${stats.downloaded} téléchargés, ${stats.already_exists} déjà présents, ${stats.skipped} ignorés, ${stats.errors} erreurs`, 'text-blue-500');
    }
}

/**
 * Process import queue sequentially
 */
async function processImportQueue() {
    console.log('DEBUG: processImportQueue START');
    console.log('DEBUG: importQueue =', importQueue);
    console.log('DEBUG: importQueue.length =', importQueue.length);
    console.log('DEBUG: isCancelled =', isCancelled);
    
    if (!importQueue || importQueue.length === 0) {
        console.error('ERROR: importQueue is empty or undefined!');
        return;
    }
    
    console.log('DEBUG: About to start for loop');
    
    for (let i = 0; i < importQueue.length; i++) {
        console.log('DEBUG: Loop iteration', i, 'of', importQueue.length);
        
        if (isCancelled) {
            console.log('DEBUG: Cancelled at iteration', i);
            break;
        }

        const track = importQueue[i];
        console.log('DEBUG: Processing track:', track);
        
        currentIndex = i + 1;

        // Update progress
        document.getElementById('currentProgress').textContent = currentIndex;
        const percentage = (currentIndex / importQueue.length) * 100;
        document.getElementById('progressBar').style.width = `${percentage}%`;
        document.getElementById('currentTrackInfo').innerHTML = `
            <span class="text-text-main font-semibold">${track.artist}</span>
            <span class="text-text-sub mx-2">—</span>
            <span class="text-text-main">${track.title}</span>
        `;

        log(`[${currentIndex}/${importQueue.length}] Traitement: ${track.artist} - ${track.title}`, 'text-gray-400');

        try {
            await processImportTrack(track);
        } catch (error) {
            console.error('Error processing track:', error);
            log(`❌ Erreur inattendue: ${error.message}`, 'text-red-500');
            stats.errors++;
            updateStats();
        }

        // Delay between requests to avoid rate limiting and bot detection
        // 2 seconds is safer than 500ms for mass imports
        await sleep(2000);
    }
}

/**
 * Process a single track
 */
async function processImportTrack(track) {
    try {
        const response = await fetch('/api/import/search-and-download', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken()
            },
            body: JSON.stringify({
                title: track.title,
                artist: track.artist,
                uri: track.uri
            })
        });

        const result = await response.json();

        if (result.success) {
            if (result.status === 'downloaded') {
                log(`  ✅ Téléchargé: ${result.video}`, 'text-green-500');
                stats.downloaded++;
            } else if (result.status === 'already_exists') {
                log(`  ℹ️ Déjà dans la bibliothèque`, 'text-blue-500');
                stats.already_exists++;
            }
        } else {
            if (result.status === 'skipped') {
                log(`  ⚠️ Ignoré: ${result.message} (${result.results_count || 0} résultats)`, 'text-yellow-500');
                stats.skipped++;
            } else if (result.status === 'no_results') {
                log(`  ⚠️ Aucun résultat YouTube`, 'text-yellow-500');
                stats.no_results++;
            } else if (result.status === 'bot_detected') {
                log(`  🤖 Bot détecté: ${result.message}`, 'text-orange-500');
                log(`  💡 Conseil: Ralentissez l'import ou attendez quelques minutes`, 'text-blue-400');
                stats.errors++;
            } else {
                log(`  ❌ Erreur: ${result.message}`, 'text-red-500');
                stats.errors++;
            }
        }

        updateStats();

    } catch (error) {
        console.error('Network error:', error);
        log(`  ❌ Erreur réseau: ${error.message}`, 'text-red-500');
        stats.errors++;
        updateStats();
    }
}

/**
 * Update statistics display
 */
function updateStats() {
    document.getElementById('statDownloaded').textContent = stats.downloaded;
    document.getElementById('statExists').textContent = stats.already_exists;
    document.getElementById('statSkipped').textContent = stats.skipped + stats.no_results;
    document.getElementById('statErrors').textContent = stats.errors;
}

/**
 * Cancel import
 */
function cancelImport() {
    if (confirm('Voulez-vous vraiment annuler l\'importation en cours ?')) {
        isCancelled = true;
        log('⏸️ Annulation en cours...', 'text-yellow-500');
    }
}

/**
 * Clear logs
 */
function clearLogs() {
    document.getElementById('importLogs').innerHTML = '';
}

/**
 * Add log message
 */
function log(message, colorClass = 'text-gray-400') {
    const logsContainer = document.getElementById('importLogs');
    const div = document.createElement('div');
    div.className = colorClass;
    
    // Add timestamp
    const timestamp = new Date().toLocaleTimeString('fr-FR');
    div.textContent = `[${timestamp}] ${message}`;
    
    logsContainer.appendChild(div);
    logsContainer.scrollTop = logsContainer.scrollHeight; // Auto scroll
}

/**
 * Sleep utility
 */
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * Get CSRF token from meta tag
 */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}
