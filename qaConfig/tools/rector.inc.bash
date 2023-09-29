# First we run the Safe Rectors to implement safe versions of functions.
# Disabling this - no simple way to ignore lines with rector and we need raw curl, not safe curl
#rectorSafeExitCode=99
#while ((rectorSafeExitCode > 1)); do
#  set +e
#  echo "Running 'Safe' Rector to convert to safe versions of functions"
#  phpNoXdebug -f "$binDir"/rector process ${pathsToCheck[@]} \
#    --config "$projectRoot/vendor/thecodingmachine/safe/rector-migrate.php"
#  rectorSafeExitCode=$?
#  set -e
#  if ((rectorSafeExitCode > 0)); then
#    tryAgainOrAbort "Rector 'Safe'"
#  fi
#done

# Then we check for project specific Rectors.
if [[ -f $projectRoot/rector.php ]]; then
  echo "Running Project Specific Rector as configured in $projectRoot/rector.php"
  if [[ -f $projectRoot/bin/console ]]; then
    (cd $projectRoot && APP_ENV=dev phpNoXdebug -f ./bin/console -- cache:clear)
  fi
  rectorExitCode=99
  while ((rectorExitCode > 1)); do
    set +e
    echo "Running Project Specific Rector"
    phpNoXdebug -f "$binDir"/rector process ${pathsToCheck[@]} \
      --config "$projectRoot/rector.php"
    rectorExitCode=$?
    set -e
    if ((rectorExitCode > 0)); then
      tryAgainOrAbort "Rector Project Specific"
    fi
  done
fi

