# To deploy, first push up new docker containers using ./build.sh
# Then, delete all the running of the service

# Lists tasks running in a given cluster
# Will return a result like:
# "{
#       "taskArns": [
#           "arn:aws:ecs:us-west-2:964400098929:task/c9374f87-0eb6-401a-9db1-1dce190f0f8f"
#       ]
#  }"
#aws ecs list-tasks --region=us-west-2 --cluster=mmm

# Stops a task
#aws ecs stop-task --region=us-west-2 --cluster=mmm --task=c9374f87-0eb6-401a-9db1-1dce190f0f8f

