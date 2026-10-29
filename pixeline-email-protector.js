// Give back the address the server withheld. Two shapes arrive here:
// a parked anchor, whose markup the author wrote and which only lacks its href,
// and a protected span, which has to become an anchor.
(function () {
    function pxln_decode(parked) {
        var query_at = parked.indexOf('?');
        var address = (query_at === -1 ? parked : parked.slice(0, query_at))
            .replace(/\([^)]*\)/, '@')
            .replace(/\s+/g, '');

        // Only the address is normalised: a subject line may legitimately hold spaces.
        return address === '' ? '' : address + (query_at === -1 ? '' : parked.slice(query_at));
    }

    var pxln_parked = document.querySelectorAll('a[data-pep-email]');
    for (var p = 0; p < pxln_parked.length; ++p) {
        var target = pxln_decode(pxln_parked[p].getAttribute('data-pep-email') || '');
        if (target !== '') {
            pxln_parked[p].setAttribute('href', 'mailto:' + target);
        }
        pxln_parked[p].removeAttribute('data-pep-email');
    }

    var pxln_emails = document.querySelectorAll('span.pep-email');
    for (var i = 0; i < pxln_emails.length; ++i) {
        var source = pxln_emails[i];
        var peptitle = source.getAttribute('title') || '';
        var pepemail = (source.textContent || '').replace(/\([^)]*\)/, '@').replace(/\s+/g, '');
        if (peptitle === '') { peptitle = pepemail; }
        var link = document.createElement('a');
        link.className = 'pep-email';
        link.setAttribute('href', 'mailto:' + pepemail);
        link.textContent = peptitle;
        source.parentNode.insertBefore(link, source);
        source.parentNode.removeChild(source);
    }
})();
