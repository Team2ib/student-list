#!/usr/bin/env python

import unittest
from student_age import get_student_age
from student_age import app

from base64 import b64encode
headers = {
    'Authorization': 'Basic %s' % b64encode(b"toto:python").decode("ascii")
}

class FlaskTestCase(unittest.TestCase):

     def setUp(self):
       app.testing = True
       self.app = app.test_client()

     def test_http_response(self):
       rv = self.app.get('/pozos/api/v1.0/get_student_ages', headers=headers)
       self.assertEqual(rv.status, '200 OK')
       self.assertIn(b'student_ages', rv.data)
