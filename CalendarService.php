<?php

namespace ChurchCRM\Service;

use ChurchCRM\PersonQuery;
use ChurchCRM\EventQuery;
use ChurchCRM\FamilyQuery;

class CalendarService
{

  function getEventTypes()
  {
    $eventTypes = array();
    array_push($eventTypes, array("Name" => "Event", "backgroundColor" =>"#f39c12" ));
    array_push($eventTypes, array("Name" => "Birthday", "backgroundColor" =>"#f56954" ));
    array_push($eventTypes, array("Name" => "Anniversary", "backgroundColor" =>"#0000ff" ));
    return $eventTypes;
  }

  function getEvents()
  {
    $events = array();

    $curYear = date("Y");
    $curMonth = date("m");
    $peopleWithBirthDays = PersonQuery::create()
      ->filterByBirthMonth(array('min' => 1)) // have birthday month
      ->filterByBirthDay(array('min' => 1)) // have birthday day
      ->find();

    foreach ($peopleWithBirthDays as $person) {
      $year = $curYear;
      if ($person->getBirthMonth() < $curMonth) {
        $year = $year + 1;
      }

      if ($person->getBirthDay() < 10 ) {
        $day="0" . $person->getBirthDay();
      } else {
        $day=$person->getBirthDay();
      }

      if ($person->getBirthMonth() < 10 ) {
        $month="0" . $person->getBirthMonth();
      } else {
        $month=$person->getBirthMonth();
      }


      $start = $year . "-" . $month . "-" . $day;
      
      $event = $this->createCalendarItem("birthday", $person->getFullName(), $start, "", $person->getViewURI());
      array_push($events, $event);
    }


    $Anniversaries = FamilyQuery::create()
      ->filterByWeddingDate(array('min' => '0001-00-00')) // a Wedding Date 
      ->find();

    foreach ($Anniversaries as $anniversary) {
      $year = $curYear;
      if ($anniversary->getWeddingMonth() < $curMonth) {
        $year = $year + 1;
      }
      $start = $year . "-" . $anniversary->getWeddingMonth() . "-" . $anniversary->getWeddingDay();

      $event = $this->createCalendarItem("anniversary", $anniversary->getName(), $start, "", $anniversary->getViewURI());

      array_push($events, $event);
    }

    $activeEvents = EventQuery::create()
      ->filterByInActive("false")
      ->orderByStart()
      ->find();
    foreach ($activeEvents as $evnt) {
      $event = $this->createCalendarItem("event", $evnt->getTitle(), $evnt->getStart("Y-m-d"), $evnt->getEnd("Y-m-d"), "");
      array_push($events, $event);
    }

    return $events;
  }


  function createCalendarItem($type, $title, $start, $end, $uri)
  {
    $event = array();
    switch ($type) {
      case "birthday":
        $event["backgroundColor"] = '#f56954';
        break;
      case "event":
        $event["backgroundColor"] = '#f39c12';
        break;
      case "anniversary":
        $event["backgroundColor"] = '#0000ff';
        break;
      default:
        $event["backgroundColor"] = '#eeeeee';
    }

    $event["title"] = $title;
    $event["start"] = $start;
    if ($end != "") {
      $event["end"] = $end;
    }
    if ($uri != "") {
      $event["url"] = $uri;
    }
    $event["allDay"] = true;

    return $event;
  }

}
