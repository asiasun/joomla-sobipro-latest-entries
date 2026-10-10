<?php
/**
 * @package    mod_sobipro_latest_entries
 * @author     F.Yousefi - https://www.asiasun.ir
 * @copyright  (C) 2026 AsiaSun.ir, Pvt. Ltd. All rights reserved
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @version    2.0.1
 */

defined('_JEXEC') or die;

class ModSobiproLatestEntriesHelper
{
	public static function decodeSobiProString($var)
	{
		if (!is_string($var) || strlen($var) < 3) {
			return null;
		}

		$decoded = base64_decode($var, true);

		if ($decoded === false) {
			$unserialized = @unserialize($var);
			return $unserialized !== false ? $unserialized : null;
		}

		if (function_exists('gzinflate')) {
			$inflated = @gzinflate($decoded);
			if ($inflated !== false) {
				$unserialized = @unserialize($inflated);
				return $unserialized !== false ? $unserialized : null;
			}
		}

		$unserialized = @unserialize($decoded);
		return $unserialized !== false ? $unserialized : null;
	}

	public static function getLatestEntries($params)
	{
		$db = JFactory::getDbo();

		$limit = max(1, (int) $params->get('items_count', 10));
		$sectionId = max(1, (int) $params->get('section_id', 1));
		$sortFieldId = max(1, (int) $params->get('sort_field_id', 49));
		$titleFieldId = max(1, (int) $params->get('title_field_id', 1));
		$coverFieldId = max(1, (int) $params->get('cover_field_id', 48));
		$categoryFieldId = max(1, (int) $params->get('category_field_id', 77));

		$allowedRaw = trim((string) $params->get('allowed_categories', ''));
		$allowedCategories = array();
		if ($allowedRaw !== '') {
			foreach (explode(',', $allowedRaw) as $cid) {
				$cid = (int) trim($cid);
				if ($cid > 0) {
					$allowedCategories[] = $cid;
				}
			}
		}

		$query = $db->getQuery(true)
			->select(array('a.sid', 'a.baseData AS sort_value'))
			->from($db->quoteName('#__sobipro_field_data', 'a'))
			->join('INNER', $db->quoteName('#__sobipro_object', 'b') . ' ON a.sid = b.id')
			->where('a.fid = ' . (int) $sortFieldId)
			->where('a.section = ' . (int) $sectionId)
			->where('b.oType = ' . $db->quote('entry'))
			->where('b.approved = 1')
			->order('a.baseData DESC');

		$db->setQuery($query, 0, $limit * 8);
		$rows = $db->loadObjectList();

		if (!is_array($rows) || !$rows) {
			return array();
		}

		$candidateIds = array();
		foreach ($rows as $row) {
			$sid = (int) $row->sid;
			if ($sid > 0 && !isset($candidateIds[$sid])) {
				$candidateIds[$sid] = true;
			}
		}
		$candidateIds = array_keys($candidateIds);

		if (!$candidateIds) {
			return array();
		}

		$idList = implode(',', $candidateIds);

		$fieldQuery = $db->getQuery(true)
			->select(array('sid', 'fid', 'baseData'))
			->from($db->quoteName('#__sobipro_field_data'))
			->where('sid IN (' . $idList . ')')
			->where('fid IN (' . (int) $titleFieldId . ',' . (int) $coverFieldId . ',' . (int) $categoryFieldId . ')');
		$db->setQuery($fieldQuery);
		$fieldRows = $db->loadObjectList();

		$fields = array();
		if (is_array($fieldRows)) {
			foreach ($fieldRows as $fr) {
				$fields[(int) $fr->sid][(int) $fr->fid] = $fr->baseData;
			}
		}

		$objQuery = $db->getQuery(true)
			->select(array('id', 'nid'))
			->from($db->quoteName('#__sobipro_object'))
			->where('id IN (' . $idList . ')');
		$db->setQuery($objQuery);
		$objRows = $db->loadObjectList('id');

		$categoryIds = array();
		$decodedCats = array();
		foreach ($candidateIds as $sid) {
			$raw = isset($fields[$sid][$categoryFieldId]) ? $fields[$sid][$categoryFieldId] : '';
			$cats = self::decodeSobiProString($raw);
			if (!is_array($cats)) {
				$cats = array();
			}
			$decodedCats[$sid] = $cats;
			if (!empty($cats[0])) {
				$categoryIds[(int) $cats[0]] = true;
			}
		}

		$catNids = array();
		if ($categoryIds) {
			$catQuery = $db->getQuery(true)
				->select(array('id', 'nid'))
				->from($db->quoteName('#__sobipro_object'))
				->where('id IN (' . implode(',', array_keys($categoryIds)) . ')');
			$db->setQuery($catQuery);
			$catRows = $db->loadObjectList();
			if (is_array($catRows)) {
				foreach ($catRows as $cr) {
					$catNids[(int) $cr->id] = (string) $cr->nid;
				}
			}
		}

		$entries = array();
		$seen = array();

		foreach ($rows as $row) {
			if (count($entries) >= $limit) {
				break;
			}

			$sid = (int) $row->sid;
			if ($sid <= 0 || isset($seen[$sid])) {
				continue;
			}
			$seen[$sid] = true;

			$cats = isset($decodedCats[$sid]) ? $decodedCats[$sid] : array();

			if ($allowedCategories) {
				$ok = false;
				foreach ($cats as $cid) {
					if (in_array((int) $cid, $allowedCategories, true)) {
						$ok = true;
						break;
					}
				}
				if (!$ok) {
					continue;
				}
			}

			$title = isset($fields[$sid][$titleFieldId]) ? (string) $fields[$sid][$titleFieldId] : '';
			$coverRaw = isset($fields[$sid][$coverFieldId]) ? $fields[$sid][$coverFieldId] : '';
			$cover = self::decodeSobiProString($coverRaw);
			$image = '';
			if (is_array($cover)) {
				$image = !empty($cover['thumb']) ? $cover['thumb'] : (!empty($cover['original']) ? $cover['original'] : '');
			}

			$nid = isset($objRows[$sid]) ? (string) $objRows[$sid]->nid : '';
			$catNid = '';
			if (!empty($cats[0]) && isset($catNids[(int) $cats[0]])) {
				$catNid = $catNids[(int) $cats[0]];
			}

			$entries[] = (object) array(
				'sid' => $sid,
				'title' => $title,
				'nid' => $nid,
				'image' => $image,
				'category_nid' => $catNid,
			);
		}

		return $entries;
	}

	public static function buildUrl($entry, $basePath)
	{
		$base = trim((string) $basePath, '/');
		$parts = array();

		if ($base !== '') {
			$parts[] = $base;
		}

		if ($entry->category_nid !== '') {
			$parts[] = $entry->category_nid;
		}

		$parts[] = $entry->sid . '-' . $entry->nid;

		return JUri::root() . implode('/', $parts);
	}
}
