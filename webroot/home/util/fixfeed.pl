#!/usr/bin/perl -w
use Data::Dumper;

sub do_post($)
{
	my $content = shift;

	my $url = "http://uchome.manyou.com/userapp/update?uch_url=http%3A%2F%2F17salsa.net%2Fhome%2Fcp.php%3Fac%3Duserapp&allow_side_nav=1&privacy=6&";

#<input type="hidden" name="sId" value='1014728'>
	my $vars = {};
	while( $content=~m#<input type="hidden" name="([^"]+)" value='([^']+)'>#sig )
	{
		$vars->{$1} = $2;
	}

	$vars->{allow_side_nav} = 1;
	$vars->{allow_feed} = 0;
	$vars->{allow_profile_link} = 0;
	$vars->{privacy} = 6;
	
#print Dumper($vars);
#die();

		$ua = LWP::UserAgent->new;
		$ua->agent("Salsa/17 ");

	foreach my $key ( keys %{$vars}  )
	{
		$url .= "$key=" . $vars->{$key};
	}

	#die($url);

my $t = [
    "ph" => "2",
    "an" => "",
    "tn" => "Codex Seraphinianus",
    "pn" => "",
    "sn" => "",
    "gpnm" => "All Book Stores",
    "cty" => "All Countries",
    "bi" => "Any Binding",
    "prl" => "",
    "prh" => "",
    "sortby" => "0",
    "ds" => "100",
    "bu" => "Start Search"
  ] ;

#print Dumper($t);
#print Dumper([%$vars]);
#die();
		my $response = $ua->post(
						$url,[%$vars]
						);

die "Error: ", $response->status_line, "\n"
 unless $response->is_success;

print "post OK\n";

}



# Create a user agent object
use LWP::UserAgent;
use LWP::Simple;

my $count = 0;
while (<STDIN>)
{
		chomp;
		$page = get($_);
		$count++;
		print "#$count Processing $_\n";
		if ( $page=~m#form action# )
		{
			do_post($page);
		}else{
			print "this user did not use this app\n";
		}
		
}


