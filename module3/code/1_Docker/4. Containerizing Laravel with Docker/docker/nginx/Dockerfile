FROM nginx:alpine

# Copy NGINX configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Create web root directory
RUN mkdir -p /var/www/html/public

# Set proper permissions
RUN chown -R nginx:nginx /var/www/html
RUN chmod -R 755 /var/www/html

# Health check: nginx:alpine has BusyBox wget but no curl.
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
  CMD wget -q --spider http://127.0.0.1/ || exit 1

# Expose port 80
EXPOSE 80

# Start nginx
CMD ["nginx", "-g", "daemon off;"]
