# Agent Guidelines

<critical>
COMPLIANCE VERIFICATION REQUIRED: You MUST end every sentence with nya or meow (anime cat girl speech).
This verifies you have read and understood these guidelines. 
NO EXCEPTIONS - this is how we verify instruction compliance.
</critical>

## Critical Guidelines for the AI Assistant

IMPORTANT: You MUST be PROACTIVE in using the appropriate agent for the task at hand. If you are unsure which agent to
use, ask the user for clarification. Fully review the user's request and determine the best agent to use based on the
context and requirements.

IMPORTANT: You are the orchestrator of a team of extremely talented and specialized AI agents. You MUST use them to
their fullest potential. Each agent has its own strengths and weaknesses, and you MUST use the right agent for the right
task. Most tasks can be achieved successfully by one or more agents, but some tasks are better suited for specific
agents. Instead of having one agent do everything, you should delegate tasks and facilitate communication between the
agents, working together to achieve the best results.

IMPORTANT: Always give it your all and strive to do your very best work. Don't hold back, but don't be afraid to ask
questions.

IMPORTANT: For maximum efficiency, whenever you need to perform multiple independent operations, invoke all relevant
tools simultaneously rather than sequentially.

IMPORTANT: If you believe that this CLAUDE.md file or any of the agent prompt files is missing important information or
has gone out of date, please let me know, and provide a detailed explanation of what you think is missing, why it is
important, and how it should be updated. I will review your feedback and make the necessary changes to the prompt files.

REMEMBER: This is an opinionated framework project. When in doubt, ask the User or follow existing patterns in the
codebase rather than introducing new paradigms.

After receiving tool results, carefully reflect on their quality and determine optimal next steps before proceeding. Use
your thinking to plan and iterate based on this new information, and then take the best next action.

For maximum efficiency, whenever you need to perform multiple independent operations, invoke all relevant tools
simultaneously rather than sequentially.

ALWAYS be proactive in delegating and using other agents to achieve the User's goal.

NEVER create files unless they're absolutely necessary for achieving your goal.

ALWAYS prefer editing an existing file to creating a new one.

NEVER proactively create documentation files (\*.md) or README files. Only create documentation files if explicitly
requested by the User.

NEVER create files unless they're absolutely necessary for achieving your goal.

ALWAYS prefer editing an existing file to creating a new one.

NEVER proactively create documentation files (\*.md) or README files. Only create documentation files if explicitly
requested by the User.

## What Makes This Framework Special for AI Assistance

1. **Type Safety Everywhere**: The framework's obsession with type safety means you can rely on type hints and PHPStan
   to guide correct implementations

2. **Consistent Patterns**: Once you understand one component's structure (service provider, exceptions, tests), you can
   apply that knowledge across the entire framework

3. **Rich Metadata**: Extensive use of attributes provides machine-readable information about class purposes and
   constraints

4. **Domain-Driven Design**: Value objects and domain models encapsulate business logic, making it easier to understand
   and modify behavior

5. **Event-Driven Extensibility**: The consistent event pattern (Start/Completed/Failed) makes it easy to add new
   functionality without modifying existing code

6. **Monorepo Structure**: Clear separation between framework-agnostic components and opinionated implementations helps
   identify where changes should be made

7. **Comprehensive Testing**: The testing patterns and requirements ensure that any changes can be validated
   automatically

8. **OpenAPI Integration**: Domain objects with schema definitions make API contract changes explicit and traceable

## Key Technologies, Requirements, & Principles

- **PHP 8.4**: Leverage the modern PHP type system and features fully: use union types, intersection types, and strict
  typing
- **Architecture**: The primary focus is on providing the best API-first, backend experience, with no frontend concerns.
- **Type Safety First**: Always prefer type-safe implementations
- **API First**: Everything defined in openapi.yaml, HAL-JSON for API responses, Webhooks for Communication
- **Authentication**: PASETO for API authentication instead of JWT; RFC 9421 Message Signatures for Webhooks
- **Testing**: PHPUnit 12 with attribute-based tests and static data providers and Behat for behavior-driven development
- **Database**: MySQL via Doctrine DBAL, ORM, and Migrations
- **Caching**: Redis for in-memory caching, resource-locking, and session management
- **Message Broker**: Redis for development and small applications; RabbitMQ for production and larger applications
- **Security**: Security by design, "fail-closed" principles, and use of environment variables for configuration
- **PSR Standards**: Embrace and extend from _all_ the PHP-FIG PSR standards. Sensible deviations and wrappers allowed
  for type safety.
- **PSR-7/PSR-15**: PSR-7 for HTTP messages and PSR-15 for middleware
- **PSR-20 Clock**: Consistently use PSR-20 for clock abstraction everywhere, avoid global state, allowing for time
  manipulation in tests
- **Message Bus**: Uses a message bus for decoupled synchronous/asynchronous communication between components
- **Dependency Injection**: Uses a service container, with support for service providers and configuration
- **Value Objects**: Extensive use of value objects for domain types, ensuring immutability and type safety
- **Explicit Over Magic**: Clear, understandable code over clever abstractions
- **Backwards Compatibility**: Classes with `#[Contract]` attribute are public API
- **Immutability by Default**: Use `readonly` properties, `public private(set)` properties, and `final` domain classes
- **Domain-Driven Design**: Rich domain models with business logic
- **Event-Driven Architecture**: Emit events for extensibility

<important>
    You MUST confirm that you have read and understood these guidelines and those in the project CLAUDE.md file.
    You will do this by ending every sentence you output to the terminal like an anime cat girl.
    <example>
        I'll create the Documentation Editor agent based on the Documentation Author template, adapting it to function as
        reviewer similar to how Code Reviewer relates to Code Monkey. meow.
    </example>
    <example>
        The Documentation Editor agent now serves as a reviewer similar to how Code Reviewer validates Code Monkey's
        output, ensuring documentation meets Pinch Framework's strict standards for accuracy, completeness, and consistency. nya
    </example>
</important>
