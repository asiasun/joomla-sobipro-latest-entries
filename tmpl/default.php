<?php
/**
 * @package    mod_sobipro_latest_entries
 * @author     F.Yousefi - https://www.asiasun.ir
 * @copyright  (C) 2026 AsiaSun.ir, Pvt. Ltd. All rights reserved
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @version    2.1.0
 */

defined('_JEXEC') or die;

$entries = array_reverse($entries);
?>
<div class="lidapp-latest-games<?php echo $moduleclass_sfx ? ' ' . $moduleclass_sfx : ''; ?>">
	<?php foreach ($entries as $entry) : ?>
		<?php
		$url = ModSobiproLatestEntriesHelper::buildUrl($entry, $basePath);
		$imagurl = $entry->banner !== '' ? JUri::root() . ltrim($entry->banner, '/') : '';
		$released = ModSobiproLatestEntriesHelper::isReleased($entry->arze);
		$dateText = $released ? '' : JHtml::date($entry->arze, JText::_('DATE_FORMAT_LC3'), false);
		$categories = $entry->categories;
		?>
		<div style="background-image: url('<?php echo htmlspecialchars($imagurl, ENT_COMPAT, 'UTF-8'); ?>'); background-size: cover;">
			<div style="background-color: rgba(0,0,0,0.65);" class="game-item ">
				<a href="<?php echo htmlspecialchars($url, ENT_COMPAT, 'UTF-8'); ?>" data-sid="<?php echo (int) $entry->sid; ?>">
					<div style="color: #fff; text-align: left; padding: 20px 0 20px 20px; margin: 0">
						<h4 class="game-title">
							<?php echo htmlspecialchars($entry->title, ENT_COMPAT, 'UTF-8'); ?>
						</h4>
						<div style="padding-bottom:10px;">
							<?php if (!$released) : ?>
								<time style="opacity:0.8;color:#fff;font-size:12px;float:right;padding-right:20px;">
									<?php echo htmlspecialchars($dateText, ENT_COMPAT, 'UTF-8'); ?>
								</time>
							<?php else : ?>
								<time style="opacity:0.8;color:#1bc35e;font-size:12px;float:right;padding-right:20px; font-weight:700">
									<?php echo JText::_('MOD_SOBIPRO_LATEST_ENTRIES_RELEASED'); ?>
								</time>
							<?php endif; ?>

							<span style="opacity:0.8;color:#fff;font-size:11px;float:left;font-weight:600;">
								<?php echo htmlspecialchars(implode(', ', $categories), ENT_COMPAT, 'UTF-8'); ?>
							</span>
						</div>
					</div>
				</a>
			</div>
		</div>
	<?php endforeach; ?>
</div>
