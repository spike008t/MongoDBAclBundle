<?php

/*
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IamPersistent\MongoDBAclBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Bundle\DoctrineAbstractBundle\DependencyInjection\AbstractDoctrineExtension;

/**
 * @author Richard Shank <develop@zestic.com>
 */
class IamPersistentMongoDBAclExtension extends AbstractDoctrineExtension
{
    /**
     * Responds to the doctrine_mongodb configuration parameter.
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Load DoctrineMongoDBBundle/Resources/config/mongodb.xml
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('security.xml');

        $processor = new Processor();
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $processor->processConfiguration($configuration, $configs);

        if (empty($config['default_database']))
            $config['default_database'] = 'mongodb_sf2_acl';
        
        if (isset($config['acl_provider'])) {
            $this->loadAcl($config['acl_provider'], $config['default_database'], $container);
        }
    }

    protected function loadAcl($config, $defaultDatabase, ContainerBuilder $container)
    {
        $database = isset($config['database']) ? $config['database'] : $defaultDatabase;
        $container->setParameter('doctrine.odm.mongodb.security.acl.database', $database);

        $container->setParameter('doctrine.odm.mongodb.security.acl.entry_collection', $config['collections']['entry']);
        $container->setParameter('doctrine.odm.mongodb.security.acl.oid_collection', $config['collections']['object_identity']);
    }

    public function getAlias()
    {
        return 'iam_persistent_mongo_db_acl';
    }

    /**
     * Returns the namespace to be used for this extension (XML namespace).
     *
     * @return string The XML namespace
     */
    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/doctrine/odm/mongodb';
    }
    
    protected function getMappingObjectDefaultName() {
        return 'Document';
    }

    protected function getMappingResourceConfigDirectory() {
        return 'Resources/config/doctrine';
    }

    protected function getMappingResourceExtension() {
        return 'mongodb.acl';
    }

    protected function getObjectManagerElementName( $name ) {
        return 'doctrine.odm.mongodb.security.acl.'.$name;
    }

}
