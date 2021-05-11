pipeline {
	environment {
		IMAGE_NAME = "student_list"
		IMAGE_TAG = "latest"
		STAGING = "team2-staging"
		PRODUCTION = "team2-production"
		IMAGE_REPO = "team2"
		IMAGE_REGISTRY = "132.145.77.137:5000"
                API_USERNAME = "toto"
                API_PASSWORD = "python"
	}
	agent none
	stages {
		stage('Build image') {
			agent {
				docker {
						image 'docker:dind'
				}
			}
			steps {
				script {
					sh 'docker build -t $IMAGE_REGISTRY/$IMAGE_REPO/$IMAGE_NAME:$IMAGE_TAG simple_api'
				}
			}
		}
                stage('run api container') {
                        agent {
                                docker {
                                                image 'docker:dind'
                                }
                        }
                        steps {
                                script {
                                        sh '''
                                        docker network create student_list
					docker run -d -p 5000:5000 -v ${PWD}/simple_api/student_age.json:/data/student_age.json --network --name student_list student_list 132.145.77.137:5000/team2/student_list
                                        docker run -d -p 80:80 --network student_list -e USERNAME=$API_USERNAME -e PASSWORD=$API_PASSWORD -v ${PWD}/website:/var/www/html --name website php:apache
					'''
                                }
                        }
                }
                stage('Test api container') {
                        agent any
                        steps {
                                script {
                                        sh '''
					curl -u toto:python http://172.17.0.1:5000/pozos/api/v1.0/get_student_ages | grep -q "alice"
                                        '''
                                }
                        }
                }
		stage('Test website container') {
			agent any
			steps {
				script {
					sh '''
					curl http://172.17.0.1 | grep -q "Student Checking App"
					'''
				}
			}
		}
	}
}
