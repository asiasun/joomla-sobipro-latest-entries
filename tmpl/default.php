<?php
/**
 * @package    mod_sobipro_latest_entries
 * @author     F.Yousefi - https://www.asiasun.ir
 * @copyright  (C) 2026 AsiaSun.ir, Pvt. Ltd. All rights reserved
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @version    2.0.1
 */

defined('_JEXEC') or die;

$itemsCount = count($entries);
?>
<div class="lidapp-latest-games<?php echo $moduleclass_sfx ? ' ' . $moduleclass_sfx : ''; ?>">
	<?php foreach ($entries as $index => $entry) : ?>
		<?php
		$url = ModSobiproLatestEntriesHelper::buildUrl($entry, $basePath);
		$isLast = ($index === $itemsCount - 1);
		$imagurl = $entry->image !== '' ? JUri::root() . $entry->image : '';
		?>
		<div class="slide<?php echo $isLast ? ' last-slide' : ''; ?>">
			<a href="<?php echo htmlspecialchars($url, ENT_COMPAT, 'UTF-8'); ?>" data-sid="<?php echo (int) $entry->sid; ?>">
				<h3 class="game-title"><?php echo htmlspecialchars($entry->title, ENT_COMPAT, 'UTF-8'); ?></h3>
				<div class="image-box">
					<img class="spFieldsData field_cover" src="<?php echo htmlspecialchars($imagurl, ENT_COMPAT, 'UTF-8'); ?>">
				</div>
			</a>
		</div>
	<?php endforeach; ?>
</div>
