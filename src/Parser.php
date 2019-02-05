<?php

class Parser
{
    public static function removeHyperLink($content)
    {

        // Removes <a href="mailto:" links sometimes added via copy/pasting into the visual editor

        $r = '`\<a([^>]+)href\=\"mailto\:([^">]+)\"([^>]*)\>(.*?)\<\/a\>`ism';
        preg_match_all($r, $content, $addresses, PREG_SET_ORDER);
        $the_addrs = isset($addresses[0]) ? $addresses[0] : array();
        $repaddr = array();
        for ($a = 0; $a < count($the_addrs); $a++) {
            $repaddr[$a] = preg_replace($r, '$2', $the_addrs[$a]);
        }
        $cc = str_replace($the_addrs, $repaddr, $content);
        return $cc;

    }

    public static function replace_email_with_obfuscation($content, $html = true)
    {
        // ----------------------------------------------------------------------
        // MAIN FUNCTION: replaces any email address by its harvest-proof counterpart.
        // ----------------------------------------------------------------------
        $addr_pattern = '/([A-Z0-9._%+-]+)@([A-Z0-9.-]+)\.([A-Z]{2,63})(\((.+?)\))?/i';
        preg_match_all($addr_pattern, $content, $addresses);
        $the_addrs = $addresses[0];
        $repaddr = array();
        for ($a = 0; $a < count($the_addrs); $a++) {
            if (!$html && count($the_addrs[$a]) == 4) {
                $repaddr[$a] = preg_replace($addr_pattern, '$5', $the_addrs[$a]);
            } else {
                $repaddr[$a] = preg_replace($addr_pattern, '<span title="$5" class="pep-email">$1(' . $this->options['pep_email_substitution_string'] . ')$2.$3</span>', $the_addrs[$a]);
            }
        }
        $cc = str_replace($the_addrs, $repaddr, $content);
        return $cc;
    }
}
