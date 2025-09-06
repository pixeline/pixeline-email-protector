var pxln_emails = document.querySelectorAll('span.pep-email');
if (typeof pxln_emails !== 'undefined') {
    for (var i = 0; i < pxln_emails.length; ++i) {
        var source = pxln_emails[i];
        var peptitle = source.getAttribute('title') || '';
        var pepemail = (source.textContent || '').replace(/\s*\(.+\)\s*/, '@');
        if (peptitle === '') { peptitle = pepemail; }
        var link = document.createElement('a');
        link.className = 'pep-email';
        link.setAttribute('href', 'mailto:' + pepemail);
        link.textContent = peptitle;
        source.parentNode.insertBefore(link, source);
        source.parentNode.removeChild(source);
    }
}