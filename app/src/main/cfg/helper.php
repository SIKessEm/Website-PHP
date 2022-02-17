<?php use Ske\Util\{
    Template,
    Http\Server,
    User,
    App,
    Locale,
    Translation,
    TranslationFile,
    Translator
};

function server(?string $root = null): Server {
    static $server;
    if (!isset($server))
        $server = new Server($root);
    return $server;
}

function user(): User {
    static $user;
    if (!isset($user)) {
        $user = new User('en-US');
    }
    return $user;
}

function app(): App {
    static $app;
    if (!isset($app)) {
        $app = new App('fr-CI', 'en-US');
    }
    return $app;
}

function vals(Locale ...$locales): Translator {
    $translations = [];
    foreach ($locales as $locale) {
        $language = $locale->getLanguage();
        $country = $locale->getCountry();
        if ($file = pathOf("app.res.values.$language-$country", '.json'))
            $translations[] = new TranslationFile("$language-$country", $file);
        else
            $translations[] = new Translation("$language-$country");
        if ($file = pathOf("app.res.values.$language", '.json'))
            $translations[] = new TranslationFile($language, $file);
        else
            $translations[] = new Translation($language);
    }
    return new Translator($translations);
}

function val(string $val, string ...$args): string {
    static $vals;
    if (!isset($vals)) {
       $vals = vals(user()->getLocale(), ...app()->getLocales());
    }
    return $vals->translate($val, ...$args);
}

function tpl(string $path, array $data = [], bool $required = true): Template {
    return new Template(pathOf("app.res.views.$path", '.php') ?: $path, $data, $required);
}

function pathOf(string $name, string $extension = '.php'): ?string {
    return server()->pathOf($name, $extension);
}

function send(null|int|string $content = null): void {
    if (!isset($content))
        exit;
    exit($content);
}

function style(string $name): string {
    return url("static.$name", '.css');
}

function script(string $name): string {
    return url("static.$name", '.js');
}

function url(string $name, string $extension): ?string {
    if (!pathOf("web.$name", $extension)) {
        throw new \RuntimeException("Unknown $name ($extension) in " . pathOf('web'));
    }
    return '/' . str_replace('.', '/', $name) . $extension;
}