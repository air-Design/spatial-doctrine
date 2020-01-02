<?php

declare(strict_types=1);

namespace Spatial\Entity;

use Config;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;

class DoctrineEntity
{
    private Configuration $_config;
    private $_cache;

    /**
     * Constructor
     *
     * @param string $domain
     */
    public function __construct(string $domain)
    {
        $this->_onInit($domain);
    }

    /**
     * Set default configs for
     * cahce and proxy with production mode.
     *
     * @param string $domain
     * @return void
     */
    private function _onInit(string $domain)
    {
        $config = new Configuration();
        // get values from config
        include_once './config.php';
        $enableProdMode = (new Config)->enableProdMode;

        // Set defaults
        if ($enableProdMode) {
            // set default to prod mode
            // change the cache to Redis
            // declare(strict_types=1);
            // use Doctrine\Common\Cache\RedisCache;
            // use Doctrine\ORM\Configuration;

            // $metadataCache = new RedisCache();
            // $configuration = new Configuration();
            // ...
            // $configuration->setMetadataCacheImpl($metadataCache);
            $cache = new MemcachedCache();
            $config->setAutoGenerateProxyClasses(false);
//            make sure to use redis
        } else {
            // set default to dev mode

            $cache = new ArrayCache();
            $config->setAutoGenerateProxyClasses(true);
        }
        // echo __DIR__;
        $domainRootPath = './src/core/domain/';

        // I might need to force valye of driver for doamin folder at constructor
        // Driver Implementation
        $driverImpl = $config
            ->newDefaultAnnotationDriver($domainRootPath . $domain);
        $config->setMetadataDriverImpl($driverImpl);

        // Cache
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);

        // Proxies
        $config->setProxyDir($domainRootPath . $domain . '/proxy');
        $config->setProxyNamespace('Core\Domain\\' . ucfirst($domain));

        $this->_config = $config;
        $this->_cache = $cache;
        // return $this;
    }

    /**
     * Return Doctrine's
     * EntityManager based on the connection string
     *
     * @param array $connectionOptions
     * @return EntityManager
     * @throws ORMException
     */
    public function entityManager(array $connectionOptions): EntityManager
    {
        return EntityManager::create($connectionOptions, $this->_config);
    }

    /**
     * Set Development True/False
     *
     * @param boolean $dev
     * @return self
     */
    public function isDev(bool $dev = false): self
    {
        if ($dev) {
            $this->_cache = new ArrayCache;
            $this->_config->setAutoGenerateProxyClasses(true);
        } else {
            $this->_cache = new MemcachedCache();
            $this->_config->setAutoGenerateProxyClasses(false);
        }
        return $this;
    }

    // Proxi Directory

    /**
     * Configuration Options
     * The following sections describe all the configuration options
     * available on a Doctrine\ORM\Configuration instance.
     *
     * @param string $dir
     * @return self
     */
    public function setProxyDir(string $dir = '/src/core/domain/media/proxies'): self
    {
        // print_r($this->_config);
        $this->_config->setProxyDir($dir);
        return $this;
    }


    // Proxy Namespace

    /**
     * Sets the namespace to use for generated proxy classes.
     *
     * @param string $namespace
     * @return self
     */
    public function setProxyNamespace(string $namespace = 'Core\Domain'): self
    {
        $this->_config->setProxyNamespace($namespace);
        return $this;
    }


    /**
     * Sets the metadata driver implementation that is used
     * by Doctrine to acquire the object-relational
     * metadata for your classes
     *
     * @param [type] $connectionOptions
     * @return self
     */
    public function setMetadataDriverImpl($driver): self
    {
        $this->_config->setMetadataDriverImpl($driver);
        return $this;
    }

}
