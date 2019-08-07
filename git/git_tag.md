git tag  列出所有tag
git tag -l "<pattern>" 列出匹配的tag
git tag -a [tag_name] 创建注释型tag，会标记出创建人名称，邮件，日期，message等
git tag [tag_name] 创建轻量级tag，只有tag名称
git tag [tag_name] [commit_hash] 对制定的commit记录进行tag，因此不一定需要实时tag
git push origin [tag_name] 推送到远程，普通push不会把tag信息推送给远程
git push origin --tags 推送所有tag到远程
git tag -d [tag_name] 删除本地tag
git push orgin --delete [tag_name] 删除远程tag
