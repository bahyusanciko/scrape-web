FROM python:3.11-slim

WORKDIR /app

# Install snscrape
RUN pip install --no-cache-dir --upgrade pip setuptools wheel \
    && pip install --no-cache-dir snscrape

CMD ["tail", "-f", "/dev/null"]
