#!/usr/bin/python

# To debug, use app.logger.debug().

from flask import Flask
from flask import request
from ansible.runner import Runner
from pipes import quote
import json

app = Flask(__name__)

@app.route('/exec/<server>/<module>', methods=['POST'])
def execute(server, module):
    args = ''
    for k,v in request.json.iteritems():
        args += ' ' + k + "=" + quote(v)

    runner = Runner(
        module_name=module,
        module_args=args,
        pattern=server,
    )
    results = runner.run()
    return json.dumps(results)

if __name__ == '__main__':
  app.run(host='0.0.0.0', debug=True)