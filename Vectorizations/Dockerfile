FROM python:3.10.6
WORKDIR /usr/src/app
COPY requirements.txt ./
RUN pip3 install  -r requirements.txt
COPY SemanticBertAPI.py . 
EXPOSE 5000
CMD [ "flask", "--app","SemanticBertAPI.py","run", "--host=0.0.0.0" ]
