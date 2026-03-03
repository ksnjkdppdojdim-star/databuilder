-- =====================================================
-- DataBuilderIMSCPPlugin - Complete Cleanup Script
-- =====================================================
-- Exécutez ce script pour supprimer complètement le plugin
-- et toutes ses tables de la base de données i-MSCP
-- =====================================================

-- 1. Supprimer les tâches en attente
DELETE FROM plugin_tasks WHERE plugin_name = 'DataBuilderIMSCPBetaPlugin';

-- 2. Supprimer l'enregistrement du plugin
DELETE FROM plugin WHERE plugin_name = 'DataBuilderIMSCPBetaPlugin';

-- 3. Supprimer les tables DataBuilder si elles existent
DROP TABLE IF EXISTS `databuilder_user_preferences`;
DROP TABLE IF EXISTS `databuilder_layouts`;
DROP TABLE IF EXISTS `databuilder_templates`;

-- 4. Supprimer les entrées dans la table urlredirection (si le plugin a créé des redirections)
DELETE FROM urlredirection WHERE `description` LIKE '%DataBuilder%';

-- 5. Vérification - Lister ce qui reste
SELECT 'plugin_tasks:' AS table_name, COUNT(*) AS count FROM plugin_tasks WHERE plugin_name = 'DataBuilderIMSCPBetaPlugin';
SELECT 'plugin:' AS table_name, COUNT(*) AS count FROM plugin WHERE plugin_name = 'DataBuilderIMSCPBetaPlugin';

-- 6. Lister toutes les tables DataBuilder restantes
SHOW TABLES LIKE 'databuilder%';
