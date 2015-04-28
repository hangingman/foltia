# ChangeLogと連動させる
dist-hook: ChangeLog

# configure.acが更新された時動く
ChangeLog:
	@echo "Non-maintainer upload" > doc/ChangeLog
	@echo "" >> doc/ChangeLog
	git log --stat --name-only --date=short --abbrev-commit >> doc/ChangeLog
