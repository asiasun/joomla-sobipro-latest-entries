<?php
/**
 * @package    mod_sobipro_latest_entries
 * @author     F.Yousefi - https://www.asiasun.ir
 * @copyright  (C) 2026 AsiaSun.ir, Pvt. Ltd. All rights reserved
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @version    2.1.0
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

		$limit = max(1, (int) $params->get('items_count', 8));
		$sectionId = max(1, (int) $params->get('section_id', 1));
		$sortFieldId = max(1, (int) $params->get('sort_field_id', 49));
		$titleFieldId = max(1, (int) $params->get('title_field_id', 1));
		$coverFieldId = max(1, (int) $params->get('cover_field_id', 48));
		$bannerFieldId = max(1, (int) $params->get('banner_field_id', 62));
		$categoryFieldId = max(1, (int) $params->get('category_field_id', 77));

		$query = $db->getQuery(true)
			->select(array('a.sid', 'a.baseData AS sort_value'))
			->from($db->quoteName('#__sobipro_field_data', 'a'))
			->join('INNER', $db->quoteName('#__sobipro_object', 'b') . ' ON a.sid = b.id')
			->where('a.fid = ' . (int) $sortFieldId)
			->where('a.section = ' . (int) $sectionId)
			->where('b.oType = ' . $db->quote('entry'))
			->where('b.approved = 1')
			->order('a.baseData DESC');

		$db->setQuery($query, 0, $limit * 5);
		$rows = $db->loadObjectList();

		if (!is_array($rows) || !$rows) {
			return array();
		}

		$sids = array();
		foreach ($rows as $row) {
			$sid = (int) $row->sid;
			if ($sid > 0 && !isset($sids[$sid])) {
				$sids[$sid] = true;
			}
		}
		$sidList = implode(',', array_keys($sids));
		if ($sidList === '') {
			return array();
		}

		$fids = array_unique(array(
			(int) $titleFieldId,
			(int) $coverFieldId,
			(int) $bannerFieldId,
			(int) $categoryFieldId,
			(int) $sortFieldId,
		));

		$fieldQuery = $db->getQuery(true)
			->select(array('sid', 'fid', 'baseData'))
			->from($db->quoteName('#__sobipro_field_data'))
			->where('sid IN (' . $sidList . ')')
			->where('fid IN (' . implode(',', $fids) . ')');
		$db->setQuery($fieldQuery);
		$fieldRows = $db->loadObjectList();

		$fields = array();
		if (is_array($fieldRows)) {
			foreach ($fieldRows as $fr) {
				$fields[(int) $fr->sid][(int) $fr->fid] = $fr->baseData;
			}
		}

		$categoryIds = array();
		$decodedCats = array();
		foreach (array_keys($sids) as $sid) {
			$raw = isset($fields[$sid][$categoryFieldId]) ? $fields[$sid][$categoryFieldId] : '';
			$cats = self::decodeSobiProString($raw);
			if (!is_array($cats)) {
				$cats = array();
			}
			$decodedCats[$sid] = $cats;
			foreach (array_slice($cats, 0, 5) as $cid) {
				$cid = (int) $cid;
				if ($cid > 0) {
					$categoryIds[$cid] = true;
				}
			}
		}

		$categoryNames = array();
		if ($categoryIds) {
			$catQuery = $db->getQuery(true)
				->select(array('id', 'name'))
				->from($db->quoteName('#__sobipro_object'))
				->where('id IN (' . implode(',', array_keys($categoryIds)) . ')');
			$db->setQuery($catQuery);
			$catRows = $db->loadObjectList();
			if (is_array($catRows)) {
				foreach ($catRows as $cr) {
					$categoryNames[(int) $cr->id] = (string) $cr->name;
				}
			}
		}

		$objQuery = $db->getQuery(true)
			->select(array('id', 'nid'))
			->from($db->quoteName('#__sobipro_object'))
			->where('id IN (' . $sidList . ')');
		$db->setQuery($objQuery);
		$objects = $db->loadObjectList('id');

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

			$cover = self::decodeSobiProString(isset($fields[$sid][$coverFieldId]) ? $fields[$sid][$coverFieldId] : '');
			$banner = self::decodeSobiProString(isset($fields[$sid][$bannerFieldId]) ? $fields[$sid][$bannerFieldId] : '');
			$arze = isset($fields[$sid][$sortFieldId]) ? $fields[$sid][$sortFieldId] : (string) $row->sort_value;

			$names = array();
			foreach (array_slice($decodedCats[$sid], 0, 5) as $cid) {
				$cid = (int) $cid;
				if ($cid > 0 && !empty($categoryNames[$cid])) {
					$names[] = $categoryNames[$cid];
				}
			}

			$entries[] = (object) array(
				'sid' => $sid,
				'title' => isset($fields[$sid][$titleFieldId]) ? (string) $fields[$sid][$titleFieldId] : '',
				'nid' => isset($objects[$sid]) ? (string) $objects[$sid]->nid : '',
				'thumb' => is_array($cover) && !empty($cover['thumb']) ? $cover['thumb'] : '',
				'banner' => is_array($banner) && !empty($banner['image']) ? $banner['image'] : '',
				'arze' => $arze,
				'categories' => $names,
			);
		}

		return $entries;
	}

	public static function buildUrl($entry, $basePath)
	{
		$base = trim((string) $basePath, '/');
		$slug = $entry->sid . '-' . $entry->nid;
		return JUri::root() . ($base !== '' ? $base . '/' : '') . $slug;
	}

	public static function isReleased($arze)
	{
		$ts = self::toTimestamp($arze);
		return $ts > 0 && $ts <= time();
	}

	public static function toTimestamp($value)
	{
		$value = trim((string) $value);
		if ($value === '') {
			return 0;
		}
		if (ctype_digit($value)) {
			return (int) $value;
		}
		$ts = strtotime($value);
		return $ts ? (int) $ts : 0;
	}
}
