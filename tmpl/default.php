<?php
/**
 * @package    mod_sobipro_latest_entries
 * @author     F.Yousefi - https://www.asiasun.ir
 * @copyright  (C) 2026 AsiaSun.ir, Pvt. Ltd. All rights reserved
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @version    2.0.0
 */

defined('_JEXEC') or die;

$itemsCount = count($entries);
?>
<div class="mod-sobipro-latest-entries<?php echo $moduleclass_sfx ? ' ' . $moduleclass_sfx : ''; ?>">
	<?php foreach ($entries as $index => $entry) : ?>
		<?php
		$url = ModSobiproLatestEntriesHelper::buildUrl($entry, $basePath);
		$isLast = ($index === $itemsCount - 1);
		$imageSrc = $entry->image !== '' ? JUri::root() . ltrim($entry->image, '/') : '';
		?>
		<div class="slide<?php echo $isLast ? ' last-slide' : ''; ?>">
			<a href="<?php echo htmlspecialchars($url, ENT_COMPAT, 'UTF-8'); ?>" data-sid="<?php echo (int) $entry->sid; ?>">
				<?php if ($entry->title !== '') : ?>
					<h3 class="entry-title"><?php echo htmlspecialchars($entry->title, ENT_COMPAT, 'UTF-8'); ?></h3>
				<?php endif; ?>
				<?php if ($imageSrc !== '') : ?>
					<div class="image-box">
						<img src="<?php echo htmlspecialchars($imageSrc, ENT_COMPAT, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($entry->title, ENT_COMPAT, 'UTF-8'); ?>">
					</div>
				<?php endif; ?>
			</a>
		</div>
	<?php endforeach; ?>
</div>
