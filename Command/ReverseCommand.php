<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Propel\Bundle\PropelBundle\Command;

use Propel\Bundle\PropelBundle\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * ReverseCommand.
 *
 * @author William DURAND <william.durand1@gmail.com>
 */
class ReverseCommand extends AbstractCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDescription('Generate XML schema from reverse-engineered database')
            ->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'Set this parameter to define a connection to use')
            ->setHelp(<<<EOT
The <info>propel:reverse</info> command generates an XML schema from reverse-engineered database.
  <info>php app/console propel:reverse</info>

The <info>--connection</info> parameter allows you to change the connection to use.
The default connection is the active connection (propel.dbal.default_connection).
EOT
            )
            ->setName('propel:reverse')
        ;

    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When the target directory does not exist
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        [$name, $defaultConfig] = $this->getConnection($input, $output);

        $ret = $this->callPhing('reverse', [
            'propel.project'            => $name,
            'propel.database'           => $defaultConfig['adapter'],
            'propel.database.url'       => $defaultConfig['connection']['dsn'],
            'propel.database.user'      => $defaultConfig['connection']['user'],
            'propel.database.password'  => $defaultConfig['connection']['password'] ?? '',
        ]);

        if (true === $ret) {
            $filesystem = new Filesystem();
            $generated  = $this->getCacheDir() . '/schema.xml';
            $filename   = $name . '_reversed_schema.xml';
            $destFile   = $this->getApplication()->getKernel()->getProjectDir() . '/var/propel/generated-schemas/' . $filename;

            if (\file_exists($generated)) {
                $filesystem->copy($generated, $destFile);
                $output->writeln([
                    '',
                    \sprintf('>>  <info>File+</info>    %s', $destFile),
                ]);
            } else {
                $output->writeln(['', 'No generated files.']);
            }
        } else {
            $this->writeTaskError($output, 'reverse');
        }

        return 0;
    }
}
