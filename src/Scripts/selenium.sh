#!/usr/bin/env bash
set -o errexit -o pipefail -o nounset
#set -o xtrace # see whats executed

usage_txt="Usage: $0 [-a start|stop] [-v verbose]"

if (($# < 2)); then
    echo "$usage_txt" >&2
    exit 1
fi

function is_action_valid {
    valid_apps=('start' 'stop' )
    for app in "${valid_apps[@]}" ; do
        if [ "$1" == "${app}" ]; then
          return 0
        fi
    done
    return 1
}

verbose=false
FIREFOX_TEMP_LOC=''
OPTIND=1
choose_action=''
while getopts ":a: v p:" opt; do
  case "$opt" in
    a)
        choose_action="$OPTARG"
        if ! is_action_valid "$choose_action" ; then
            echo "Invalid Action. $usage_txt" >&2
            exit 1
        fi
        ;;
    v)
        verbose=true ;;
    p)
        PROFILE_DIR="$OPTARG"
        if [ ! -d "$PROFILE_DIR" ]; then
            echo -e "The Firefox profile firectory given does not exists -- \n"$PROFILE_DIR
            echo "$usage_txt" >&2
            exit 1
        fi
        FIREFOX_TEMP_LOC='-firefoxProfileTemplate '$PROFILE_DIR;;
    \?)
        echo "$usage_txt" >&2
        exit 1;;
    :)
        echo "Option -$OPTARG requires an argument." >&2
        exit 1;;
  esac
done
shift $((OPTIND-1))

touch selenium.log

selenium_loc='/opt/selenium-server-standalone.jar'
verbose_end='> selenium.log 2> selenium.log &'

if [ "$choose_action" = "start" ] ; then
    full_cmd="DISPLAY=:1 /usr/bin/xvfb-run --auto-servernum --server-num=1 java -jar $selenium_loc $FIREFOX_TEMP_LOC $verbose_end"
    echo -n "Starting Selenium RC..."
    eval "$full_cmd"
    if [ "$verbose" = true ] ; then
        tail -f selenium.log
    else
        echo -n " Waiting..."
        sleep 5
        echo "Done!"
    fi
elif [ "$choose_action" = "stop" ] ; then
    echo -n "Stoping Selenium RC..."
    curl -s http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer
    sudo killall -9 Xvfb || true
    echo "Done!"
else
    echo "Wrong Action?!"
fi
