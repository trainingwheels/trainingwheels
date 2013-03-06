<?php

namespace TrainingWheels\Common;
use Closure;

class Observable {
  protected $observers = array();

  protected function fireEvent($eventName, array $data = NULL) {
    if (isset($this->observers[$eventName])) {
      foreach ($this->observers[$eventName] as $observer) {
        $observer($data);
      }
    }
  }

  public function addObserver($eventName, Closure $observer) {
    if (!isset($this->observers[$eventName])) {
      $this->observers[$eventName] = array();
    }
    $this->observers[$eventName][] = $observer;
  }

  public function removeObserver($eventName, Closure $observer) {
    if (isset($this->observers[$eventName])) {
      foreach ($this->observers[$eventName] as $key => $existingObserver) {
        if ($existingObserver === $observer) {
          unset($this->observers[$eventName][$key]);
        }
      }
    }
  }
}
