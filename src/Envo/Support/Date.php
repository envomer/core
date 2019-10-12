<?php 

namespace Envo\Support;

class Date
{
	const FORMAT_DATE = 1;
	const FORMAT_DATETIME = 2;

	protected static $months = [
		'January', 'February', 'March', 'April', 'May', 'June', 'July',
		'August', 'September', 'October', 'November', 'December'
	];

	public static function diff($date1, $date2 = null, $format = null)
	{
		if ( ! $date1 ) {
            $date1 = date('Y-m-d H:i:s');
        }

		if ( ! $date2 ) {
            $date2 = date('Y-m-d H:i:s');
        }

	    $datediff = strtotime($date1) - strtotime($date2);
	    if ( ($hours = floor($datediff/(60*60*24))) ) {
            return $hours . ' ' . \_t('app.day', null, $hours);
        }
	    if ( ($hours = floor($datediff/(60*60))) ) {
            return $hours  . ' ' . \_t('app.hour', null, $hours);
        }
	    if ( ($hours = floor($datediff/(60))) ) {
            return $hours  . ' ' . \_t('app.minute', null, $hours);
        }
	    if ( ($hours = floor($datediff)) ) {
            return $hours  . ' ' . \_t('app.second', null, $hours);
        }

	    return -1;
	}

	public static function month($month)
	{
		return \_t('locale.months')[$month];
	}
	
	public static function datetime($date, $format = 'Y-m-d H:i:s')
	{
		if ( ! is_numeric($date) ) {
            $date = strtotime($date);
        }
		return date($format, $date);
	}

	public static function day($date)
	{
		return date('d.m.Y', strtotime($date));
	}

	public static function validate($date, $format = null)
	{
		$format = $format ?: 'd.m.Y';
		$dateInt = strtotime($date);
		if ( date($format, $dateInt) == $date ) return date('Y-m-d', $dateInt);
		return false;
	}
	
	public static function now()
	{
		return date('Y-m-d H:i:s');
	}
	
	public static function inBetween($start, $end, $interval = '1 month', $format = 'Ym', $wholeMonth = true)
	{
		$start    = (new DateTime($start));
		$end      = (new DateTime($end));
		if ( $wholeMonth ) {
			$start = $start->modify('first day of this month');
			$end = $end->modify('first day of next month');
		}
		$interval = DateInterval::createFromDateString($interval);
		$period   = new DatePeriod($start, $interval, $end);

		$months = array();
		foreach ($period as $dt) {
			$months[$dt->format($format)] = $dt->format($format);
		}
		
		return $months;
	}

	public static function getUserFormat($format = self::FORMAT_DATE, $javascript = false)
	{
		if ( $javascript ) {
			if ($format == self::FORMAT_DATE) {
                return 'dd.MM.yyyy';
            }

			return 'dd.MM.yyyy HH:mm';
		}
		if ($format == self::FORMAT_DATE) {
            return 'd.m.Y';
        }

		return 'd.m.Y H:i';
	}

	public static function userDate($date = null)
	{
		if ( $date && ! is_integer($date) ) {
            $date = strtotime($date);
        }

		return date(self::getUserFormat(self::FORMAT_DATE), $date);
	}

	public static function userDatetime($date = null)
	{
		if ( $date && ! is_integer($date) ) {
            $date = strtotime($date);
        }

		return date(self::getUserFormat(self::FORMAT_DATETIME), $date);
	}

}