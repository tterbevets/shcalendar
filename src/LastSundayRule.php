<?php
declare(strict_types=1);

namespace SHCalendar;

use SHCalendar\Rule;

class LastSundayRule implements Rule
{

  public function create(\DateTime $date): array
  {
    if ($date < \DateTime::createFromFormat('Y-m-d', '1800-01-01')) {
      throw new \InvalidArgumentException('Date must be 1800-01-01 or after. Got [' . $date->format('Y-m-d') .']');
    }
    $rule = ['BYDAY' => '-SU',
      'OFFSET' => '0'
    ];

    $nextSunday = clone $date;
    $nextSunday->modify('this sunday');

    $rule['BYMONTH'] = $nextSunday->format('n');

    return $rule;
  }

}


?>
