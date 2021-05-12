pipeline {
	environment {
		JENKINS_CONTAINER_NETWORK = "jenkins_default"
		IMAGE_NAME = "student_list"
                FRONT_CONTAINER_NAME = "website"
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
					docker rm -vf ${IMAGE_NAME} || echo 0
					docker run -d -v ${PWD}/simple_api/student_age.json:/data/student_age.json --network $JENKINS_CONTAINER_NETWORK --name $IMAGE_NAME $IMAGE_REGISTRY/$IMAGE_REPO/$IMAGE_NAME:$IMAGE_TAG 
					'''
                                }
                        }
                }
                stage('Test api container') {
                        agent any
                        steps {
                                script {
                                        sh '''
					curl -u toto:python http://$IMAGE_NAME:5000/pozos/api/v1.0/get_student_ages | grep -q "alice"
                                        '''
                                }
                        }
                }
                stage('run website container') {
                        agent {
                                docker {
                                                image 'docker:dind'
                                }
                        }
                        steps {
                                script {
                                        sh '''
					docker rm -vf ${FRONT_CONTAINER_NAME} || echo 0
					docker run -d --network $JENKINS_CONTAINER_NETWORK -e USERNAME=$API_USERNAME -e PASSWORD=$API_PASSWORD -v ${PWD}/website:/var/www/html --name $FRONT_CONTAINER_NAME php:apache
					'''
                                }
                        }
                }
		stage('Test website container') {
			agent any
			steps {
				script {
					sh '''
					curl http://$FRONT_CONTAINER_NAME | grep -q "Student Checking App"
					'''
				}
			}
		}
		stage('Clean Container') {
			agent any
			steps {
				script {
					sh '''
					docker rm -vf ${IMAGE_NAME}
                                        docker rm -vf ${FRONT_CONTAINER_NAME}
					'''
				}
			}
		}
		stage('Ansible deploy staging') {
			agent { 
				docker {
					image 'registry.gitlab.com/robconnolly/docker-ansible:latest' 
				} 
			}
			environment {
				SSH_SECRET = credentials('ssh_private_key_2')
			}
			steps {
				script {
					sh 'cd ansible'
					sh 'cp \$SSH_SECRET id_rsa'
					sh '''
					chmod 600 id_rsa
					cat id_rsa
					ls -lrat
					id
					pwd
					ansible all -m ping -i staging.yml --private-key id_rsa -vvv
					ansible-playbook -i staging.yml install-docker.yml --private-key id_rsa -vvv
					ansible-playbook -i staging.yml student_list.yml --private-key id_rsa -vvv
					'''
				}
			}
		}
		stage('Tests staging deployment') {
			agent {
				docker {
					image 'registry.gitlab.com/robconnolly/docker-ansible:latest'
				}
			}
			environment {
				SSH_SECRET = credentials('ssh_private_key')
			}
			steps {
				script {
					sh '''
					cd ansible
					echo \$SSH_SECRET > id_rsa
					chmod 600 id_rsa
					ansible-playbook -i staging.yml tests.yml --private-key id_rsa
					'''
				}
			}
		}
		stage('Ansible deploy production') {
			agent { 
				docker {
					image 'registry.gitlab.com/robconnolly/docker-ansible:latest' 
				} 
			}
			environment {
				SSH_SECRET = credentials('ssh_private_key')
			}
			steps {
				script {
					sh '''
					cd ansible
					echo \$SSH_SECRET > id_rsa
					chmod 600 id_rsa
					ansible-playbook -i production.yml install-docker.yml --private-key id_rsa
					ansible-playbook -i production.yml student_list.yml --private-key id_rsa
					'''
				}
			}
		}
		stage('Tests staging production') {
			agent {
				docker {
					image 'registry.gitlab.com/robconnolly/docker-ansible:latest'
				}
			}
			environment {
				SSH_SECRET = credentials('ssh_private_key')
			}
			steps {
				script {
					sh '''
					cd ansible
					echo \$SSH_SECRET > id_rsa
					chmod 600 id_rsa
					ansible-playbook -i production.yml tests.yml --private-key id_rsa
					'''
				}
			}
		}
	}
}
