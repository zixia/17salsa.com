#!/bin/sh

now=`perl -e 'print time()'`
num=`perl -e '
use DBI; 
use DBD::mysql;
use strict;

my $dbh = DBI->connect("DBI:mysql:database=17salsa;host=localhost",	"root", "");
my $sth = $dbh->prepare("SELECT count(*) as num FROM uchome_session");
$sth->execute();
my $ref = $sth->fetchrow_hashref();
my $num = $ref->{'num'};
$sth->finish();
$dbh->disconnect();
print $num;
'`

rrdtool update online.rrd $now:$num
