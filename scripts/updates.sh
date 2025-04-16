!/bin/bash

if [ $# -gt 0 ]; then
    version=$1
else
    read -p "Enter software version: " version
fi

if [ -z "$version" ]; then
    echo "Error: No version provided!" >&2
    exit 1
fi

echo "Configuring version: $version"
kubectl set image deployment/hospital-mis-depl hospital-mis-depl=mubaraklouis/hospitalsystem:$version