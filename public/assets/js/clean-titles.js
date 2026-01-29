/**
 * Clean all song titles in the database
 * Removes HTML entities and unwanted keywords from titles, artists, and albums
 */
async function cleanAllTitles() {
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    
    // Confirm action
    if (!confirm('This will clean all song titles in your library by:\n\n' +
                 '• Removing HTML entities (&#201; → É)\n' +
                 '• Removing official mentions like (Official Video)\n' +
                 '• Cleaning up whitespace\n\n' +
                 'This action cannot be undone. Continue?')) {
        return;
    }

    try {
        // Disable button and show loading state
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cleaning...';

        const response = await fetch('/api/music/clean-titles', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken()
            }
        });

        const result = await response.json();

        if (result.success) {
            // Show success message with statistics
            const message = `
                🎉 Cleaning completed successfully!
                
                Total songs: ${result.total}
                Updated: ${result.updated}
                Unchanged: ${result.unchanged}
                ${result.errors > 0 ? `Errors: ${result.errors}` : ''}
            `;

            alert(message);

            // Reload page to show updated titles
            if (result.updated > 0) {
                window.location.reload();
            }
        } else {
            alert('Error cleaning titles: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error cleaning titles:', error);
        alert('An error occurred while cleaning titles. Please try again.');
    } finally {
        // Restore button state
        button.disabled = false;
        button.innerHTML = originalHTML;
    }
}
