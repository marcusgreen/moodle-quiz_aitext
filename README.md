# Moodle Quiz AI Text Report Plugin

This plugin provides an AI-powered analysis report for Moodle quiz submissions, leveraging AI to generate insights and summaries from student responses and comments.

## Features

- **AI-Powered Analysis**: Uses AI to analyze student submissions and generate comprehensive insights
- **Comment Aggregation**: Collects and summarizes student comments from quiz attempts
- **Multi-Backend Support**: Compatible with different AI backend providers:
  - local_ai_manager (mebis)
  - core_ai_subsystem (Moodle 4.5+)
  - tool_aimanager
- **Student Response Analysis**: Displays detailed student submission data including:
  - Student information (name, user ID)
  - Question details (type, text)
  - Response content and formatting
  - Scoring information (fraction, max mark, percentage)
- **AI Comment Summary**: Generates AI-powered summaries of collected student comments

## Installation

1. Download or clone this plugin into your Moodle `mod/quiz/report/` directory
2. Navigate to Site administration > Notifications to complete the installation
3. Configure the AI backend in Site administration > Plugins > Question types > AI Text

## Configuration

### AI Backend Selection

The plugin supports multiple AI backend providers. Configure your preferred backend in the plugin settings:

- **local_ai_manager**: For mebis integration
- **core_ai_subsystem**: For Moodle 4.5+ built-in AI features
- **tool_aimanager**: For tool_aimanager integration

### Required Permissions

Ensure that the AI subsystem has appropriate permissions configured for the course context where the quiz is used.

## Usage

1. Navigate to your quiz
2. Click on "Reports" in the quiz administration menu
3. Select "AI Text" from the available report options
4. Click "Generate Analysis" to process student submissions
5. View the AI-generated comment summary and detailed student response data

## Requirements

- Moodle 4.0 or higher
- One of the supported AI backend providers installed and configured
- Appropriate permissions for AI subsystem access

## Technical Details

The plugin creates an AI bridge instance with the current module context, ensuring proper permission handling and context-aware AI processing. Student submissions are processed to extract responses and comments, which are then analyzed by the configured AI backend to generate meaningful insights.

## License

This plugin is licensed under the GNU General Public License v3.0 or later.
