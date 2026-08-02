<?php
namespace App\Services;

use DOMDocument;
use DOMElement;

final class HtmlSanitizer
{
    private array $allowed = ['p'=>[],'br'=>[],'strong'=>[],'em'=>[],'u'=>[],'s'=>[],'h2'=>[],'h3'=>[],'h4'=>[],'blockquote'=>[],'ul'=>[],'ol'=>[],'li'=>[],'a'=>['href','title','target','rel'],'div'=>['class'],'figure'=>['class','data-width'],'figcaption'=>[],'img'=>['src','alt','title'],'table'=>[],'thead'=>[],'tbody'=>[],'tr'=>[],'th'=>['scope'],'td'=>[],'hr'=>[]];

    private array $discardWithContent = ['script', 'style', 'iframe', 'object', 'embed', 'template', 'noscript'];

    public function clean(?string $html): string
    {
        if (! $html) return '';
        $doc = new DOMDocument('1.0','UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><div id="root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $root = $doc->getElementById('root');
        if (! $root) return '';
        $this->walk($root);
        $output=''; foreach ($root->childNodes as $node) $output .= $doc->saveHTML($node);
        return trim($output);
    }

    private function walk($node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) continue;
            $tag=strtolower($child->tagName);
            if (in_array($tag, $this->discardWithContent, true)) { $child->remove(); continue; }
            if (! isset($this->allowed[$tag])) {
                $this->walk($child);
                $child->replaceWith(...iterator_to_array($child->childNodes));
                continue;
            }
            foreach (iterator_to_array($child->attributes) as $attribute) if (! in_array(strtolower($attribute->name),$this->allowed[$tag],true)) $child->removeAttribute($attribute->name);
            if ($tag==='div') {
                $classes = array_intersect(
                    preg_split('/\s+/', trim($child->getAttribute('class'))) ?: [],
                    ['profile-hamlet-grid', 'profile-hamlet-card'],
                );
                if ($classes) $child->setAttribute('class', implode(' ', $classes)); else $child->removeAttribute('class');
            }
            if ($tag==='a') { $href=$child->getAttribute('href'); if ($href && ! preg_match('~^(https?://|mailto:|tel:|/)~i',$href)) $child->removeAttribute('href'); if ($child->getAttribute('target')==='_blank') $child->setAttribute('rel','noopener noreferrer'); }
            if ($tag==='img' && ! preg_match('~^(https?://|/)~i',$child->getAttribute('src'))) $child->removeAttribute('src');
            if ($tag==='figure') {
                $class = trim($child->getAttribute('class'));
                if (preg_match('/(?:^|\s)align-(left|right|full|center)(?:\s|$)/i', $class, $match)) {
                    $alignment = 'align-'.strtolower($match[1]);
                } else {
                    $alignment = 'align-center';
                }
                $child->setAttribute('class', 'article-image '.$alignment);
            }
            if ($tag==='figure') { $width=(int)$child->getAttribute('data-width'); $child->setAttribute('data-width',(string)(in_array($width,range(20,100,5),true)?$width:70)); }
            $this->walk($child);
        }
    }
}
