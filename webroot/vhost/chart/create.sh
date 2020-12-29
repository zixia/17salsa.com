#!/bin/sh

rrdtool create online.rrd \
	DS:online_num:GAUGE:900:0:1000 \
	RRA:AVERAGE:0.5:1:9600 \
	RRA:AVERAGE:0.5:4:9600 \
	RRA:AVERAGE:0.5:24:6000


