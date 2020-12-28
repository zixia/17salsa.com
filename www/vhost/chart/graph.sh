rrdtool graph online.png \
	--title="http://17salsa.com registered user daily online number" \
	-w 600 -h 200 \
	--alt-autoscale-max \
	--lower-limit 0 \
	--vertical-label "Num" \
	DEF:num=online.rrd:online_num:AVERAGE \
	COMMENT:"                    Max    Min    Now\\n" \
	AREA:num#00EE00:"Online User Num" \
	GPRINT:num:MAX:"%3.0lf %s" \
	GPRINT:num:MIN:"%3.0lf %s" \
	GPRINT:num:LAST:"%3.0lf\\n"

rrdtool graph online-week.png \
	--title="http://17salsa.com registered user weekly online number" \
	-w 600 -h 200 \
	-s -1week \
	--alt-autoscale-max \
	--lower-limit 0 \
	--vertical-label "Num" \
	DEF:num=online.rrd:online_num:AVERAGE \
	COMMENT:"                    Max    Min    Now\\n" \
	AREA:num#00EE00:"Online User Num" \
	GPRINT:num:MAX:"%3.0lf %s" \
	GPRINT:num:MIN:"%3.0lf %s" \
	GPRINT:num:LAST:"%3.0lf\\n"

rrdtool graph online-month.png \
	--title="http://17salsa.com registered user monthly online number" \
	-w 600 -h 200 \
	-s -1month \
	--alt-autoscale-max \
	--lower-limit 0 \
	--vertical-label "Num" \
	DEF:num=online.rrd:online_num:AVERAGE \
	COMMENT:"                    Max    Min    Now\\n" \
	AREA:num#00EE00:"Online User Num" \
	GPRINT:num:MAX:"%3.0lf %s" \
	GPRINT:num:MIN:"%3.0lf %s" \
	GPRINT:num:LAST:"%3.0lf\\n"

rrdtool graph online-year.png \
	--title="http://17salsa.com registered user yearly online number" \
	-w 600 -h 200 \
	-s -1year \
	--alt-autoscale-max \
	--lower-limit 0 \
	--vertical-label "Num" \
	DEF:num=online.rrd:online_num:AVERAGE \
	COMMENT:"                    Max    Min    Now\\n" \
	AREA:num#00EE00:"Online User Num" \
	GPRINT:num:MAX:"%3.0lf %s" \
	GPRINT:num:MIN:"%3.0lf %s" \
	GPRINT:num:LAST:"%3.0lf\\n"

