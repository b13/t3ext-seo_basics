<?php
namespace B13\SeoBasics\UpgradeWizard;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Class ExtSeoMigration
 *
 * Upgrade Wizard to perform a migration to the EXT:seo
 */
class ExtSeoUpgradeWizard implements UpgradeWizardInterface, ChattyInterface
{
    /**
     * @var string
     */
    protected $table = 'pages';

    /**
     * @var StreamOutput
     */
    protected $output;

    /**
     * Return the identifier for this wizard
     * This should be the same string as used in the ext_localconf class registration
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'seo_basics';
    }

    /**
     * Return the speaking name of this wizard
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Migrate database fields of seo_basics to EXT:seo.';
    }

    /**
     * Return the description for this wizard
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'With TYPO3 9LTS a basic SEO functionality found its way into the core'
            . ' therefore the development of the extension seo_basics is discontinued. To'
            . ' provide a smooth migration this UpgradeWizard will migrate all database fields'
            . ' to the new core fields. Note that you might have to adjust your code if you'
            . ' accessed any of the old fields tx_seo_titletag, tx_seo_canonicaltag or tx_seo_robots.'
            . ' Make sure EXT:seo is installed and the database fields of EXT:seo are already added to the database.';
    }

    /**
     * Execute the update
     * Called when a wizard reports that an update is necessary
     *
     * @return bool
     */
    public function executeUpdate(): bool
    {
        if (ExtensionManagementUtility::isLoaded('seo') === false) {
            $this->output->writeln('EXT:seo is not installed. Aborting.');
            return false;
        }

        if ($this->coreFieldsExists() === false) {
            $this->output->writeln(
                'The database fields of EXT:seo do not exist. Please update your database'
                . ' tables first. DO NOT REMOVE anything yet.'
            );
            return false;
        }

        $this->migrateFields();
        return true;
    }

    /**
     * Is an update necessary?
     * Is used to determine whether a wizard needs to be run.
     * Check if data for migration exists.
     *
     * @return bool
     */
    public function updateNecessary(): bool
    {
        $updateNeeded = false;
        // Check if the database table even exists
        if ($this->checkIfWizardIsRequired()) {
            $updateNeeded = true;
        }
        return $updateNeeded;
    }

    /**
     * Returns an array of class names of Prerequisite classes
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [];
    }

    /**
     * Check if there are record within "pages" database table with an empty "slug" field.
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    protected function checkIfWizardIsRequired(): bool
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable($this->table);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $numberOfEntries = $queryBuilder
            ->count('uid')
            ->from($this->table)
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->isNotNull('tx_seo_titletag'),
                        $queryBuilder->expr()->neq('tx_seo_titletag', $queryBuilder->createNamedParameter('', \PDO::PARAM_STR))
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->isNotNull('tx_seo_canonicaltag'),
                        $queryBuilder->expr()->neq('tx_seo_canonicaltag', $queryBuilder->createNamedParameter('', \PDO::PARAM_STR))
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->isNotNull('tx_seo_robots'),
                        $queryBuilder->expr()->gt('tx_seo_robots', 0)
                    )
                )
            )
            ->execute()
            ->fetchColumn();
        return $numberOfEntries > 0;
    }

    /**
     * Setter injection for output into upgrade wizards
     *
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * @return bool
     */
    protected function coreFieldsExists(): bool
    {
        $databaseConnection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->table);
        $columns = $databaseConnection->getSchemaManager()->listTableColumns($this->table);
        foreach (['seo_title', 'canonical_link', 'no_index', 'no_follow'] as $columnName) {
            if (isset($columns[$columnName]) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return void
     */
    protected function migrateFields()
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable($this->table);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder->update($this->table)
            ->set('seo_title', $queryBuilder->quoteIdentifier('tx_seo_titletag'), false)
            ->where(
                $queryBuilder->expr()->isNotNull('tx_seo_titletag'),
                $queryBuilder->expr()->neq('tx_seo_titletag', $queryBuilder->createNamedParameter('', \PDO::PARAM_STR))
            )
            ->execute();

        $queryBuilder = $connectionPool->getQueryBuilderForTable($this->table);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder->update($this->table)
            ->set('canonical_link', $queryBuilder->quoteIdentifier('tx_seo_canonicaltag'), false)
            ->where(
                $queryBuilder->expr()->isNotNull('tx_seo_canonicaltag'),
                $queryBuilder->expr()->neq('tx_seo_canonicaltag', $queryBuilder->createNamedParameter('', \PDO::PARAM_STR))
            )
            ->execute();

        $queryBuilder = $connectionPool->getQueryBuilderForTable($this->table);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder->update($this->table)
            ->set('no_index', 1)
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('tx_seo_robots', 1),
                    $queryBuilder->expr()->eq('tx_seo_robots', 2)
                )
            )
            ->execute();

        $queryBuilder = $connectionPool->getQueryBuilderForTable($this->table);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder->update($this->table)
            ->set('no_follow', 1)
            ->where(
                $queryBuilder->expr()->eq('tx_seo_robots', 3)
            )
            ->execute();
    }
}
