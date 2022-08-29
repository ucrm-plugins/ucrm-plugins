#!/bin/bash

# Allow PostgreSQL
ufw allow 5432/tcp

# Allow Code Server
ufw allow 8080/tcp
