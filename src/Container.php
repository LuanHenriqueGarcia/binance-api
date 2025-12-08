<?php

namespace BinanceAPI;

/**
 * Container simples de injeção de dependências
 */
class Container
{
    /** @var array<string,callable> */
    private static array $bindings = [];

    /** @var array<string,object> */
    private static array $instances = [];

    /**
     * Registra uma factory para uma interface/classe
     *
     * @param string $abstract Nome da interface/classe
     * @param callable $factory Factory que cria a instância
     */
    public static function bind(string $abstract, callable $factory): void
    {
        self::$bindings[$abstract] = $factory;
    }

    /**
     * Registra uma instância singleton
     *
     * @param string $abstract Nome da interface/classe
     * @param object $instance Instância já criada
     */
    public static function singleton(string $abstract, object $instance): void
    {
        self::$instances[$abstract] = $instance;
    }

    /**
     * Resolve uma dependência
     *
     * @template T
     * @param class-string<T> $abstract Nome da interface/classe
     * @return T Instância resolvida
     * @throws \RuntimeException Se não encontrar binding
     */
    public static function resolve(string $abstract)
    {
        // Se já tem uma instância singleton, retorna
        if (isset(self::$instances[$abstract])) {
            return self::$instances[$abstract];
        }

        // Se tem um binding, cria a instância
        if (isset(self::$bindings[$abstract])) {
            $instance = call_user_func(self::$bindings[$abstract]);
            self::$instances[$abstract] = $instance;
            return $instance;
        }

        // Tenta criar a classe diretamente
        if (class_exists($abstract)) {
            $instance = new $abstract();
            self::$instances[$abstract] = $instance;
            return $instance;
        }

        throw new \RuntimeException("Unable to resolve: {$abstract}");
    }

    /**
     * Verifica se existe um binding para a chave
     */
    public static function has(string $abstract): bool
    {
        return isset(self::$bindings[$abstract]) || isset(self::$instances[$abstract]);
    }

    /**
     * Remove todos os bindings e instâncias
     */
    public static function flush(): void
    {
        self::$bindings = [];
        self::$instances = [];
    }

    /**
     * Registra os bindings padrão da aplicação
     */
    public static function bootstrap(): void
    {
        // Cache
        self::bind(Contracts\CacheInterface::class, fn() => new FileCache());

        // Rate Limiter
        self::bind(Contracts\RateLimiterInterface::class, fn() => new RateLimiter());

        // Client Binance
        self::bind(Contracts\ClientInterface::class, fn() => new BinanceClient());
    }
}
