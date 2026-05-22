<?php

// =====================================
// GENERATE SEO TITLE
// =====================================

function seoTitle(
    $title = '',
    $siteName = APP_NAME
)
{
    return !empty($title)

    ? $title . ' | ' . $siteName

    : $siteName;
}

// =====================================
// GENERATE META DESCRIPTION
// =====================================

function seoDescription(
    $description = '',
    $limit = 160
)
{
    $description =
    strip_tags($description);

    if(strlen($description) > $limit){

        return substr(
            $description,
            0,
            $limit
        ) . '...';
    }

    return $description;
}

// =====================================
// GENERATE SLUG
// =====================================

function generateSlug($text)
{
    $slug = strtolower($text);

    $slug = preg_replace(
        '/[^a-z0-9\s-]/',
        '',
        $slug
    );

    $slug = preg_replace(
        '/[\s-]+/',
        '-',
        $slug
    );

    return trim($slug, '-');
}

// =====================================
// CANONICAL URL
// =====================================

function canonicalUrl($path = '')
{
    return base_url($path);
}

// =====================================
// OPEN GRAPH IMAGE
// =====================================

function ogImage($image = '')
{
    if(empty($image)){

        return base_url(
            'assets/images/default-og.jpg'
        );
    }

    return base_url($image);
}

// =====================================
// META TAGS
// =====================================

function renderSeoTags($data = [])
{
    $title =
    $data['title'] ?? APP_NAME;

    $description =
    $data['description']
    ?? '';

    $keywords =
    $data['keywords']
    ?? '';

    $image =
    $data['image']
    ?? '';

    $url =
    $data['url']
    ?? canonicalUrl();

    echo '

        <title>' .
        htmlspecialchars($title) .
        '</title>

        <meta name="description" content="' .
        htmlspecialchars($description) .
        '">

        <meta name="keywords" content="' .
        htmlspecialchars($keywords) .
        '">

        <link rel="canonical" href="' .
        htmlspecialchars($url) .
        '">

        <meta property="og:title" content="' .
        htmlspecialchars($title) .
        '">

        <meta property="og:description" content="' .
        htmlspecialchars($description) .
        '">

        <meta property="og:image" content="' .
        htmlspecialchars(
            ogImage($image)
        ) .
        '">

        <meta property="og:url" content="' .
        htmlspecialchars($url) .
        '">

        <meta property="og:type" content="website">
    ';
}

// =====================================
// SCHEMA MARKUP
// =====================================

function localBusinessSchema()
{
    return '

    <script type="application/ld+json">

    {

        "@context":"https://schema.org",

        "@type":"ConstructionCompany",

        "name":"' . APP_NAME . '",

        "url":"' . base_url() . '",

        "logo":"' .
        base_url('assets/images/logo.png')
        . '",

        "telephone":"+91XXXXXXXXXX",

        "address":{

            "@type":"PostalAddress",

            "addressLocality":"Bengaluru",

            "addressCountry":"IN"
        }
    }

    </script>
    ';
}

// =====================================
// NOINDEX META
// =====================================

function noIndex()
{
    echo '

        <meta name="robots"
        content="noindex,nofollow">
    ';
}
?>