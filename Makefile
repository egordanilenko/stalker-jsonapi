BUILD      = $(shell git log --oneline | wc -l | sed -e "s/[ \t]*//g")
RELEASE_NAME := tvip-jsonapi-plugin-b$(BUILD)
build:
	@mkdir -p build
	@echo Making release: $(RELEASE_NAME)
	@mkdir -p build/tvip-jsonapi-plugin
	@echo copying...
	@rsync -avq  --delete --delete-excluded . build/tvip-jsonapi-plugin/ --exclude ".git/" --exclude ".idea/" --exclude "build/"
	@echo $(RELEASE_NAME) > build/tvip-jsonapi-plugin/BUILD
	@echo Compressing: tvip-jsonapi-plugin.tgz
	@cd build && rm -f tvip-jsonapi-plugin.tgz && tar czf tvip-jsonapi-plugin.tgz tvip-jsonapi-plugin && rm -rf tvip-jsonapi-plugin && cp tvip-jsonapi-plugin.tgz $(RELEASE_NAME).tgz
	@rm -rf tvip-jsonapi-plugin
	@echo Finished