import os
from PIL import Image

# Define paths
image_path = r"C:\Users\haser\.gemini\antigravity\brain\6fe9fd2f-8538-44d9-85ba-434fba9d58ca\media__1781648941178.png"
output_dir = r"C:\Users\haser\.gemini\antigravity\scratch\corner-flag-arena\assets"
os.makedirs(output_dir, exist_ok=True)
output_path = os.path.join(output_dir, "logo.png")

if not os.path.exists(image_path):
    print(f"Error: Source image not found at {image_path}")
    exit(1)

img = Image.open(image_path)
width, height = img.size
print(f"Image dimensions: {width}x{height}")

# Let's crop the circular logo from the jersey (chest, top-right)
# Let's estimate coordinates based on the image size:
# The logo is in the upper right quadrant, round circle.
# Let's crop a box:
# X: 52% to 75%
# Y: 15% to 30%
left = int(width * 0.54)
top = int(height * 0.155)
right = int(width * 0.74)
bottom = int(height * 0.28)

print(f"Jersey logo crop box: ({left}, {top}, {right}, {bottom})")
cropped = img.crop((left, top, right, bottom))
cropped.save(output_path)
print(f"Cropped logo saved to {output_path}")
