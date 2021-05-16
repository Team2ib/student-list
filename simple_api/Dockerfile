FROM python:2.7-stretch

LABEL maintainer="team2@team2.com"

COPY . /

RUN apt-get update -y && \
  apt-get install -y \
  python-dev=2.7.13-2 \
  python3-dev=3.5.3-1 \
  libsasl2-dev=2.1.27\* \
  libldap2-dev=2.4.44\* \
  libssl-dev=1.1.0l\* && \
  pip install --no-cache-dir -r requirements.txt

EXPOSE 5000

CMD [ "python", "./student_age.py" ]

