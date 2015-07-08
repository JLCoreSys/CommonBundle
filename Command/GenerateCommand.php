<?php

namespace CoreSys\CommonBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use CoreSys\CommonBundle\Bundle\BundleMetadata;

class GenerateCommand extends ContainerAwareCommand
{

    protected $bundleSkeletonDir;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName( 'coresys:metronic:generate' )
             ->setHelp( <<<EOT
The <info>coresys:metronic:generate</info> command generating a valid bundle structure from a Vendor Bundle.
  <info>ie: ./app/console coresys:metronic:generate CoreSysCommonBundle</info>
EOT
             );
        $this->setDescription( 'Create necessary folders to copy Metronic assets for the twig templates' );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $kernel                  = $this->getContainer()->get( 'kernel' );
        $this->bundleSkeletonDir = $kernel->locateResource( '@CoreSysCommonBundle/Resource/skeleton/bundle' );
        $dest                    = $kernel->getRootDir() . '/../src';
        $configuration           = array(
            'application_dir' => sprintf( '%s/Application', $dest )
        );
        $bundleName              = 'CoreSysCommonBundle';
        $processed               = $this->generate( $bundleName, $configuration, $output );
        if ( !$processed ) {
            $output->writeln( sprintf( '<error>The bundle \'%s\' does not exist or not defined in the kernel file!</error>', $bundleName ) );

            return -1;
        }
        $output->writeln( 'done!' );
    }

    protected function generate( $bundleName, array $configuration, $output )
    {
        $processed = FALSE;
        foreach ( $this->getContainer()->get( 'kernel' )->getBundles() as $bundle ) {
            if ( $bundle->getName() != $bundleName ) {
                continue;
            }

            $processed      = TRUE;
            $bundleMetadata = new BundleMetadata( $bundle, $configuration );
            $output->writeln( sprintf( 'Processing bundle : "<info>%s</info>"', $bundleMetadata->getName() ) );
            $this->generateBundleDirectory( $output, $bundleMetadata );
            $this->generateBundleFile( $output, $bundleMetadata );

            $output->writeln( '' );
        }

        return $processed;
    }

    protected function generateBundleDirectory( OutputInterface $output, BundleMetadata $bundleMetadata )
    {
        $directories = array( '', 'Resources/public' );
        foreach ( $directories as $directory ) {
            $dir = sprintf( '%s%s', $bundleMetadata->getExtendedDirectory(), $directory );
            if ( !is_dir( $dir ) ) {
                $output->writeln( sprintf( '  > generating bundle directory <comment>%s</comment>', $dir ) );
                mkdir( $dir, 0755, TRUE );
            }

            if ( $directory != '' ) {
                $origin = realpath( $this->bundleSkeletonDir . $directory );
                if ( FALSE !== $origin ) {
                    $this->recurseCopy( $origin, $dir );
                }
            }
        }
    }

    protected function generateBundleFile( OutputInterface $output, BundleMetadata $bundleMetadata )
    {
        $file = sprintf( '%s/Application%s.php', $bundleMetadata->getExtendedDirectory(), $bundleMetadata->getExtendedName() );
        if ( is_file( $file ) ) {
            return;
        }
        $output->writeln( sprintf( '  > generating bundle file <comment>%s</comment>', $file ) );
        $bundleTemplate = file_get_contents( $this->bundleSkeletonDir . '/bundle.mustache' );
        $string         = $this->mustache( $bundleTemplate, array(
            'bundle'        => $bundleMetadata->getExtendedName(),
            'extended_from' => $bundleMetadata->getName(),
            'namespace'     => $bundleMetadata->getExtendedNamespace(),
        ) );
        file_put_contents( $file, $string );
    }

    protected function mustache( $string, array $parameters )
    {
        $replacer = function ( $match ) use ( $parameters ) {
            return isset( $parameters[ $match[ 1 ] ] ) ? $parameters[ $match[ 1 ] ] : $match[ 0 ];
        };

        return preg_replace_callback( '/{{\s*(.+?)\s*}}/', $replacer, $string );
    }

    protected function recurseCopy( $src, $dst )
    {
        $dir = opendir( $src );
        @mkdir( $dst );
        while ( FALSE !== ( $file = readdir( $dir ) ) ) {
            if ( ( $file != '.' ) && ( $file != '..' ) ) {
                if ( is_dir( $src . '/' . $file ) ) {
                    $this->recurseCopy( $src . '/' . $file, $dst . '/' . $file );
                } else {
                    copy( $src . '/' . $file, $dst . '/' . $file );
                }
            }
        }
        closedir( $dir );
    }
}