<?php
declare(strict_types=1);

namespace SHCalendar;
use RRule\RRule;

class Rule
{
  /**
	 * Weekdays numbered from 1 (ISO-8601 or `date('N')`).
	 * Used internally but public if a reference list is needed.
	 *
	 * @todo should probably be protected, with a static getter instead
	 * to avoid unintended modification
	 *
	 * @var array The name as the key
	 */
	protected static $week_day_abbrev = array(
		'MO' => 'Monday',
		'TU' => 'Tuesday',
		'WE' => 'Wednesday',
		'TH' => 'Thursday',
		'FR' => 'Friday',
		'SA' => 'Saturday',
		'SU' => 'Sunday'
  );

  protected static $week_days = array(
		1 => 'Monday',
		2 => 'Tuesday',
		3 => 'Wednesday',
		4 => 'Thursday',
		5 => 'Friday',
		6 => 'Saturday',
		0 => 'Sunday'
  );

  public function readable(array $rule): string
  {
    // TODO use a separate validate() function here?
    if (!isset($rule['BYMONTH']) )
    {
      throw new \InvalidArgumentException('BYMONTH is required.');
    }
    if (!isset($rule['BYDAY']) )
    {
      throw new \InvalidArgumentException('BYDAY is required.');
    }
    if ( strlen($rule['BYDAY']) < 3 )
    {
      throw new \InvalidArgumentException('BYDAY format incorrect. Got [' . $rule['BYDAY'] . ']');
    }
    
    // Validate using RRule
    try 
    {
      $rrule = new RRule([
        'FREQ' => 'YEARLY',
        'INTERVAL' => 1,
        'BYMONTH' => $rule['BYMONTH'],
        'BYDAY' => $rule['BYDAY'],
        'DTSTART' => '1800-01-01',
        'COUNT' => '2'
    ]);
    }
    catch (Exception $e)
    {
      throw new \InvalidArgumentException($e);
    }

    if (!isset($rule['OFFSET']) )
    {
      $rule['OFFSET'] = 0;
    }
    if ( is_int($rule['OFFSET']) == false )
    {
      throw new \InvalidArgumentException('OFFSET format incorrect. Got [' . $rule['OFFSET'] . ']');
    }
    if (abs($rule['OFFSET']) > 7 )
    {
      throw new \InvalidArgumentException('OFFSET must be between -7 and 7. Got [' . $rule['OFFSET'] . ']');
    }
    $dateObj   = \DateTime::createFromFormat('!m', sprintf("%02s", $rule['BYMONTH']) );
    $monthName = $dateObj->format('F');
    $offset = '';

    $dayName = $this::$week_day_abbrev[substr($rule['BYDAY'], -2)];

     if ($rule['OFFSET'] !== 0)
    {
      $weekDay = $rule['OFFSET'] + \RRule\RRule::$week_days[substr($rule['BYDAY'], -2)];
      $weekDay = ($weekDay + 7) % 7;

      $offset = $this::$week_days[$weekDay] . ' before the ';
    }

    $ordinal = substr($rule['BYDAY'], 0, -2);
    $formatter = new \NumberFormatter('en_US', \NumberFormatter::SPELLOUT);
    $formatter->setTextAttribute(\NumberFormatter::DEFAULT_RULESET, "%spellout-ordinal");
    $ordinal = $formatter->format($ordinal);

    if (substr($rule['BYDAY'], 0, 1) == '-')
    {
      $ordinal = 'last';
    }

    return 'The ' . $offset . $ordinal . ' ' . $dayName . ' in ' . $monthName;
  }

}


?>
