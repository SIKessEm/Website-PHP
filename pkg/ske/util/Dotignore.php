<?php namespace Ske\Util;

class Dotignore implements \IteratorAggregate, \Countable, \ArrayAccess {
    public function __construct(array $patterns = []) {
        $this->setPatterns($patterns);
    }

    protected array $patterns = [];

    public function setPatterns(array $patterns) {
        $this->patterns = [];
        foreach ($patterns as $pattern) {
            $this->addPattern($pattern);
        }
    }

    public function addPatterns(array $patterns) {
        foreach ($patterns as $pattern) {
            $this->addPattern($pattern);
        }
    }

    public function addPattern($pattern) {
        $this->patterns[] = $pattern;
    }

    public function getPatterns(): array {
        return $this->patterns;
    }

    public function isIgnored(string $name): bool {
        $name = str_replace('\\', '/', $name);
        $name = rtrim($name, '/');
        $ignored = false;
        foreach ($this->getPatterns() as $pattern) {
            if (str_starts_with($pattern, '#')) {
                continue;
            }
            if (str_starts_with($pattern, '!')) {
                if ($this->match(substr($pattern, 1), $name)) {
                    $ignored = false;
                }
                continue;
            }
            if (str_starts_with($pattern, '/')) {
                if ($this->match($pattern, $name))
                    $ignored = true;
                continue;
            }
            if ($this->match($pattern, basename($name)))
                $ignored = true;
        }
        return $ignored;
    }

    public static function match(string $pattern, string $name): bool {
        if (str_ends_with($pattern, '/')) {
            return self::match(substr($pattern, 0, -1), $name) || self::match($pattern . '*', $name);
        }
        return fnmatch($pattern, $name);
    }

    public function loadFile(string $file): self {
        if (empty($file)) {
            throw new \InvalidArgumentException('File name cannot be empty');
        }

        if (!\is_file($file)) {
            throw new \InvalidArgumentException("$file does not exist");
        }

        if (!\is_readable($file)) {
            throw new \InvalidArgumentException("$file is not readable");
        }
        $this->setPatterns(\file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        return $this;
    }

    public function getIterator(): \Traversable {
        return new \ArrayIterator($this->patterns);
    }

    public function count(): int {
        return \count($this->patterns);
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->patterns[$offset]);
    }

    public function offsetGet(mixed $offset): mixed {
        return $this->patterns[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        $this->patterns[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void {
        unset($this->patterns[$offset]);
    }

    public function __toString(): string {
        return implode(PHP_EOL, $this->patterns);
    }

    public function __debugInfo(): array {
        return $this->patterns;
    }
}