#
# Credit: Huan LI <zixia@zixia.net> github.com/huan
#
.PHONY: test
test:
	./scripts/test.sh

.PHONY: build
build:
	docker build -t 17salsa.com .

.PHONY: run
run:
	docker run \
		--name 17salsa.com \
		--rm \
		-ti \
		-e SALSA17_MYSQL_HOST \
		-e SALSA17_MYSQL_USER \
		-e SALSA17_MYSQL_PASS \
		-e SALSA17_MYSQL_DATABASE \
		-p 8080:80 \
		-v /tmp:/var/www/admin/UploadFiles/ \
		--entrypoint bash \
		17salsa.com

.PHONY: clean
clean:
	docker rmi 17salsa.com

.PHONY: version
version:
	./scripts/version.sh