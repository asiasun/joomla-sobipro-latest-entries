<?php
/**
 * @package    mod_sobipro_latest_entries
 * @author     F.Yousefi - https://www.asiasun.ir
 * @copyright  (C) 2026 AsiaSun.ir, Pvt. Ltd. All rights reserved
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @version    2.0.1
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$entries = ModSobiproLatestEntriesHelper::getLatestEntries($params);
$moduleclass_sfx = htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_COMPAT, 'UTF-8');
$basePath = (string) $params->get('entry_base_path', 'game-profile');

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'modules/mod_sobipro_latest_entries/assets/css/style.css');

require JModuleHelper::getLayoutPath('mod_sobipro_latest_entries', $params->get('layout', 'default'));
