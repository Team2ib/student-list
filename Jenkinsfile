pipeline {
	environment {
		IMAGE_NAME = "student_list"
		IMAGE_TAG = "latest"
		STAGING = "team2-staging"
		PRODUCTION = "team2-production"
		IMAGE_REPO = "team2"
		IMAGE_REGISTRY = "132.145.77.137:5000"
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
					sh 'docker build -t $IMAGE_REGISTRY/$IMAGE_REPO/$IMAGE_NAME:$IMAGE_TAG .'
				}
			}
		}
	}
}
