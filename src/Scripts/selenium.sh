#!/usr/bin/env bash
set -o errexit -o pipefail -o nounset
#set -o xtrace # see whats executed

USAGE_TXT="Usage: $0 [-a start|stop] [-v verbose]"

if (($# < 2)); then
    echo "$USAGE_TXT" >&2
    exit 1
fi

function is_action_valid {
    VALID_ACTIONS=('start' 'stop' )
    for APP in "${VALID_ACTIONS[@]}" ; do
        if [ "$1" == "${APP}" ]; then
          return 0
        fi
    done
    return 1
}

VERBOSE=false
FIREFOX_TEMP_LOC=''
OPTIND=1
CHOOSE_ACTION=''
while getopts ":a: v p:" OPT; do
  case "$OPT" in
    a)
        CHOOSE_ACTION="$OPTARG"
        if ! is_action_valid "$CHOOSE_ACTION" ; then
            echo "Invalid Action. $USAGE_TXT" >&2
            exit 1
        fi
        ;;
    v)
        VERBOSE=true ;;
    p)
        PROFILE_DIR="$OPTARG"
        if [ ! -d "$PROFILE_DIR" ]; then
            echo -e "The Firefox profile firectory given does not exists -- \n"$PROFILE_DIR
            echo "$USAGE_TXT" >&2
            exit 1
        fi
        FIREFOX_TEMP_LOC='-firefoxProfileTemplate '$PROFILE_DIR;;
    \?)
        echo "$USAGE_TXT" >&2
        exit 1;;
    :)
        echo "Option -$OPTARG requires an argument." >&2
        exit 1;;
  esac
done
shift $((OPTIND-1))

touch selenium.log

SELENIUM_LOC='/opt/selenium-server-standalone.jar'
VERBOSE_END='> selenium.log 2> selenium.log &'

if [ "$CHOOSE_ACTION" = "start" ] ; then
    FULL_CMD="DISPLAY=:1 /usr/bin/xvfb-run --auto-servernum --server-num=1 java -jar $SELENIUM_LOC $FIREFOX_TEMP_LOC $VERBOSE_END"
    echo -n "Starting Selenium RC..."
    eval "$FULL_CMD"
    if [ "$VERBOSE" = true ] ; then
        tail -f selenium.log
    else
        echo -n " Waiting..."
        sleep 5
        echo "Done!"
    fi
elif [ "$CHOOSE_ACTION" = "stop" ] ; then
    echo -n "Stoping Selenium RC..."
    curl -s http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer
    sudo killall -9 Xvfb || true
    echo "Done!"
else
    echo "Wrong Action?!"
fi
