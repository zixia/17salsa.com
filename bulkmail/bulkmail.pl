#!/usr/bin/perl -w

use strict;
# http://packages.sw.be/perl-Mail-Bulkmail/
use Mail::Bulkmail;
use Mail::Bulkmail::Server;
use MIME::Base64;
use Getopt::Std;
use DBI;
use DBD::mysql;
use Data::Dumper;

=pod
Options
-c charset 邮件编码，如UTF-8
-s subject 邮件标题
-f eml文件路径，outlook express生成
-l 发送列表，测试的时候可以编辑一份列表，群发的时候用"-l db"
-o 发送人地址，显示在mail from中

另1，需要修改文件中的boundary变量，从eml文件中找
另2，先测试163/qq/gmail/hotmail/sina/sohu/yahoo等邮箱显示正常再群发
另3，支持邮件中嵌入图像
=cut

=pod
http://www.faqs.org/rfcs/rfc2047
http://blog.roodo.com/rocksaying/archives/2950655.html
    return $subject;
=cut
sub _buildSubject {
    my ($subject, $charset) = @_;
    my $b64subject = encode_base64($subject);
    chomp $b64subject;
    return '=?' . $charset . '?B?' . $b64subject . '?=';
}

sub _buildServer {
    my ($smtp, $port, $domain) = @_;
    my $server = Mail::Bulkmail::Server->new(
        'Smtp' => $smtp,
        'Port' => $port
    ) or die Mail::Bulkmail::Server->error();

    $server->Tries(5);
    $server->Domain($domain);
    $server->connect or die $server->error();
    return $server;
}

sub _buildMail {
    my ($server, $list, $from, $subject, $message, $charset) = @_;
    $subject = _buildSubject($subject, $charset);
    my $bulk = Mail::Bulkmail->new(
        'LIST'      => $list,
        'From'      => _buildSubject($from, 'utf-8') . ' <noreply@17salsa.com>',
        'Subject'   => $subject,
        'message_from_file' => 1,
        'headers_from_message'  => 1,
        'Message'   => $message,
        'servers'   => [$server, ],
    ) or die Mail::Bulkmail->error();
    $bulk->header("MIME-Version", "1.0");
    $bulk->header("Content-type", 'multipart/related; type="multipart/alternative"; boundary="----=_NextPart_000_0000_01CB8CFA.CBE02C50"');
    return $bulk;
}

sub _buildList {
    my ($db, $table) = ('17salsa', 'uchome_spacefield');
    my ($host, $port, $user, $pass) = ('127.1', '3306', 'root');
    my $dsn = "DBI:mysql:database=$db;host=$host;port=$port";
    my $dbh = DBI->connect($dsn, $user, $pass,
        {'RaiseError' => 1}
    ) or die "unable to connect : $DBI::errstr\n";

    my $newlist = '/tmp/bulkmail.' . $$;
    open FD, ">", $newlist;
    ##unlink $newlist;

	# select email from uchome_spacefield where email is not null
    my $sth = $dbh->prepare("SELECT email FROM " . $table . " WHERE email!=''");
    $sth->execute();

    my ($ref, $entry) = ();
	my %email = {};

    while ($ref = $sth->fetchrow_hashref()) {
        $entry = $ref->{'email'};
        chomp $entry;
        if ($entry=~m!.*?\@.*?!) {
			$email{$entry} = 1;
        }       
    }

    $sth->finish();

	$sth = $dbh->prepare("SELECT email FROM 17salsa_vip WHERE email!=''");
	$sth->execute();

	while ($ref = $sth->fetchrow_hashref()) 
	{
		$entry = $ref->{'email'};
		chomp $entry;
		if ($entry=~m!.*?\@.*?!)
		{
			$email{$entry} = 1;
		}
	}
	$sth->finish();

	foreach ( keys %email )
	{
		print FD $_, "\n";
	}

    close FD;
    $dbh->disconnect();

    return $newlist;
}

my %opts = ();
my $forceCharset = 'utf8';
my ($charset, $subject, $message) = ($forceCharset);
my ($smtpServer, $smtpPort, $smtpDomain) = ('127.0.0.1', 25, '17salsa.com');
my ($mailList, $mailFrom) = ('/tmp/bulkmail.list', '17SALSA');

getopts('c:s:f:l:o:', \%opts);

=pod
Getopt
=cut
$charset = $opts{'c'} if defined $opts{'c'};
$subject = $opts{'s'} if defined $opts{'s'};
$message = $opts{'f'} if defined $opts{'f'};
$mailList= $opts{'l'} if defined $opts{'l'};
$mailFrom= $opts{'o'} if defined $opts{'o'};

if ($mailList eq 'db') {
    $mailList = _buildList();
}


my $server = _buildServer($smtpServer, $smtpPort, $smtpDomain);
#print " _buildMail($server, $mailList, $mailFrom, $subject, $message, $forceCharset);\n";
my $bulk = _buildMail($server, $mailList, $mailFrom, $subject, $message, $forceCharset);
$bulk->bulkmail() or die $bulk->error;

0;
