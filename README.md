# FSCBatch

[![Build Status](https://secure.travis-ci.org/TheFootballSocialClub/FSCBatch.png?branch=master)](http://travis-ci.org/TheFootballSocialClub/FSCBatch)

PHP 5.3 library to help you run huge batch.

## Examples

```php
<?php

use FSC\Batch\Batch;
use Pagerfanta\Adapter\ArrayAdapter;
use Symfony\Component\Console\Output\ConsoleOutput;

$output = new ConsoleOutput();

$passwords = range(1, 100);
$hashes = array();

$batch = new Batch(new ArrayAdapter($passwords), function ($context) use (&$hashes) {
    $hashes[] = crypt($context, '$2a$10$');
});

$batch->run(10, $output);
```

Would output


```
$ php examples/array_closure.php
Batch run start. 100 jobs [Mem: 0.52 MB]
[ 10/100] [ 10.00 %] ([Δ 0.83 sec] - [Elapsed 0.83 sec] - [Remaining   7 secs]) [Mem:  0.52 MB]
[ 20/100] [ 20.00 %] ([Δ 0.83 sec] - [Elapsed    1 sec] - [Remaining   6 secs]) [Mem:  0.52 MB]
[ 30/100] [ 30.00 %] ([Δ 0.83 sec] - [Elapsed   2 secs] - [Remaining   5 secs]) [Mem:  0.52 MB]
[ 40/100] [ 40.00 %] ([Δ 0.83 sec] - [Elapsed   3 secs] - [Remaining   4 secs]) [Mem:  0.52 MB]
[ 50/100] [ 50.00 %] ([Δ 0.82 sec] - [Elapsed   4 secs] - [Remaining   4 secs]) [Mem:  0.52 MB]
[ 60/100] [ 60.00 %] ([Δ 0.83 sec] - [Elapsed   4 secs] - [Remaining   3 secs]) [Mem:  0.52 MB]
[ 70/100] [ 70.00 %] ([Δ 0.83 sec] - [Elapsed   5 secs] - [Remaining   2 secs]) [Mem:  0.79 MB]
[ 80/100] [ 80.00 %] ([Δ 0.83 sec] - [Elapsed   6 secs] - [Remaining    1 sec]) [Mem:  0.79 MB]
[ 90/100] [ 90.00 %] ([Δ 0.83 sec] - [Elapsed   7 secs] - [Remaining 0.83 sec]) [Mem:  0.79 MB]
[100/100] [100.00 %] ([Δ 0.82 sec] - [Elapsed   8 secs] - [Remaining    0 sec]) [Mem:  0.79 MB]
Batch run end. took 0.82 sec [Mem: 0.79 MB]
```
