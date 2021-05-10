FROM python:2.7-stretch

LABEL maintainer="team2@team2.com"

ADD . /

VOLUME /data

RUN apt-get update -y && apt-get install python-dev python3-dev libsasl2-dev python-dev libldap2-dev libssl-dev -y
RUN pip install flask flask_httpauth flask_simpleldap python-dotenv

RUN mkdir /data
RUN cp student_age.json /data/student_age.json

EXPOSE 5000

CMD [ "python", "./student_age.py" ]

