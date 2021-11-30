# Robo task to merge JUnit XML files

[![CircleCI](https://circleci.com/gh/Sweetchuck/robo-junit-merger/tree/1.x.svg?style=svg)](https://circleci.com/gh/Sweetchuck/robo-junit-merger/?branch=1.x)
[![codecov](https://codecov.io/gh/Sweetchuck/robo-junit-merger/branch/1.x/graph/badge.svg?token=HSF16OGPyr)](https://app.codecov.io/gh/Sweetchuck/robo-junit-merger/branch/1.x)


## Install

`composer require --dev sweetchuck/robo-junit-merger`


## Task - taskJunitMerge

```php
<?php

class RoboFile extends \Robo\Tasks
{
    use \Sweetchuck\Robo\JunitMerger\JunitMergerTaskLoader;
}
```
